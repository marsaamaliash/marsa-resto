<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Resep\Rst_RecipeComponent;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use Illuminate\Support\Facades\DB;

class RecipeBomService
{
    public function addComponent(int $versionId, array $data): Rst_RecipeComponent
    {
        $version = Rst_RecipeVersion::findOrFail($versionId);

        if ($version->approval_status !== 'draft') {
            throw new \Exception('Komponen hanya bisa ditambahkan pada versi dengan status draft.');
        }

        if (isset($data['component_recipe_id']) && $data['component_recipe_id']) {
            $this->validateNoCircularReference($version->recipe_id, $data['component_recipe_id']);
        }

        $maxLineNo = Rst_RecipeComponent::where('recipe_version_id', $versionId)
            ->withTrashed()
            ->max('line_no') ?? 0;

        return DB::connection('sccr_resto')->transaction(function () use ($versionId, $data, $maxLineNo) {
            return Rst_RecipeComponent::create([
                'recipe_version_id' => $versionId,
                'line_no' => $data['line_no'] ?? ($maxLineNo + 10),
                'component_kind' => $data['component_kind'] ?? 'item',
                'component_item_id' => $data['component_kind'] === 'item' ? $data['component_item_id'] : null,
                'component_recipe_id' => ($data['component_kind'] ?? 'item') === 'recipe' ? $data['component_recipe_id'] : null,
                'stage_code' => $data['stage_code'] ?? 'main',
                'qty_standard' => $data['qty_standard'],
                'uom_id' => $data['uom_id'],
                'wastage_pct_standard' => $data['wastage_pct_standard'] ?? 0,
                'is_optional' => $data['is_optional'] ?? false,
                'is_modifier_driven' => $data['is_modifier_driven'] ?? false,
                'substitution_group_code' => $data['substitution_group_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function updateComponent(int $componentId, array $data): Rst_RecipeComponent
    {
        $component = Rst_RecipeComponent::findOrFail($componentId);
        $version = Rst_RecipeVersion::findOrFail($component->recipe_version_id);

        if ($version->approval_status !== 'draft') {
            throw new \Exception('Komponen hanya bisa diubah pada versi dengan status draft.');
        }

        if (isset($data['component_recipe_id']) && $data['component_recipe_id']) {
            $this->validateNoCircularReference($version->recipe_id, $data['component_recipe_id']);
        }

        $data['updated_by'] = auth()->id();

        if (($data['component_kind'] ?? $component->component_kind) === 'item') {
            $data['component_recipe_id'] = null;
        } else {
            $data['component_item_id'] = null;
        }

        $component->update($data);

        return $component->fresh();
    }

    public function deleteComponent(int $componentId): bool
    {
        $component = Rst_RecipeComponent::findOrFail($componentId);
        $version = Rst_RecipeVersion::findOrFail($component->recipe_version_id);

        if ($version->approval_status !== 'draft') {
            throw new \Exception('Komponen hanya bisa dihapus pada versi dengan status draft.');
        }

        $component->deleted_by = auth()->id();
        $component->save();

        return $component->delete();
    }

    public function validateNoCircularReference(int $recipeId, int $subRecipeId): void
    {
        if ($recipeId === $subRecipeId) {
            throw new \Exception('Resep tidak boleh merujuk ke dirinya sendiri (circular reference).');
        }

        $visited = [$recipeId];
        $this->checkCircularRefRecursive($subRecipeId, $visited);
    }

    private function checkCircularRefRecursive(int $recipeId, array &$visited): void
    {
        if (in_array($recipeId, $visited)) {
            throw new \Exception('Circular reference terdeteksi: resep ini sudah ada dalam rantai BOM.');
        }

        $visited[] = $recipeId;

        $activeVersion = Rst_RecipeVersion::where('recipe_id', $recipeId)
            ->where('is_active', true)
            ->first();

        if (! $activeVersion) {
            return;
        }

        $subComponents = Rst_RecipeComponent::where('recipe_version_id', $activeVersion->id)
            ->where('component_kind', 'recipe')
            ->whereNotNull('component_recipe_id')
            ->get();

        foreach ($subComponents as $sub) {
            $this->checkCircularRefRecursive($sub->component_recipe_id, $visited);
        }
    }

    public function resolveBomTree(int $versionId, int $depth = 0): array
    {
        if ($depth > 10) {
            return [];
        }

        $components = Rst_RecipeComponent::where('recipe_version_id', $versionId)
            ->with(['componentItem', 'componentRecipe', 'uom'])
            ->orderBy('line_no')
            ->get();

        $tree = [];
        foreach ($components as $component) {
            $node = [
                'id' => $component->id,
                'line_no' => $component->line_no,
                'component_kind' => $component->component_kind,
                'qty_standard' => $component->qty_standard,
                'uom' => $component->uom?->name ?? '-',
                'wastage_pct_standard' => $component->wastage_pct_standard,
                'is_optional' => $component->is_optional,
                'depth' => $depth,
                'children' => [],
            ];

            if ($component->component_kind === 'item') {
                $node['name'] = $component->componentItem?->name ?? '-';
                $node['sku'] = $component->componentItem?->sku ?? '-';
                $node['cost_standard'] = $component->componentItem?->cost_standard ?? 0;
            } else {
                $node['name'] = $component->componentRecipe?->recipe_name ?? '-';
                $node['recipe_code'] = $component->componentRecipe?->recipe_code ?? '-';

                $subActiveVersion = Rst_RecipeVersion::where('recipe_id', $component->component_recipe_id)
                    ->where('is_active', true)
                    ->first();

                if ($subActiveVersion) {
                    $node['children'] = $this->resolveBomTree($subActiveVersion->id, $depth + 1);
                }
            }

            $tree[] = $node;
        }

        return $tree;
    }

    public function calculateMaterialCost(int $versionId): float
    {
        $components = Rst_RecipeComponent::where('recipe_version_id', $versionId)
            ->with('componentItem', 'componentRecipe')
            ->get();

        $totalCost = 0;

        foreach ($components as $component) {
            $qty = (float) $component->qty_standard;
            $wastage = (float) $component->wastage_pct_standard;
            $effectiveQty = $qty * (1 + $wastage / 100);

            if ($component->component_kind === 'item') {
                $unitCost = (float) ($component->componentItem?->cost_standard ?? 0);
                $totalCost += $effectiveQty * $unitCost;
            } else {
                $subCost = $this->getRecipeCost($component->component_recipe_id);
                $totalCost += $effectiveQty * $subCost;
            }
        }

        return round($totalCost, 4);
    }

    public function getRecipeCost(int $recipeId): float
    {
        $activeVersion = Rst_RecipeVersion::where('recipe_id', $recipeId)
            ->where('is_active', true)
            ->first();

        if (! $activeVersion) {
            return 0;
        }

        $totalMaterialCost = $this->calculateMaterialCost($activeVersion->id);
        $expectedOutput = (float) ($activeVersion->expected_output_qty ?: 1);

        if ($expectedOutput <= 0) {
            return $totalMaterialCost;
        }

        return round($totalMaterialCost / $expectedOutput, 4);
    }
}
