<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeComponent;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use Illuminate\Support\Facades\DB;

class RecipeVersionService
{
    /**
     * Create new version for a recipe
     * Simplified: No approval workflow, version is created as inactive
     */
    public function createVersion(int $recipeId, array $data): Rst_RecipeVersion
    {
        return DB::connection('sccr_resto')->transaction(function () use ($recipeId, $data) {
            $recipe = Rst_Recipe::findOrFail($recipeId);

            // Auto-generate version number
            $lastVersion = Rst_RecipeVersion::where('recipe_id', $recipeId)
                ->withTrashed()
                ->max('version_no');
            $versionNo = ($lastVersion ?? 0) + 1;

            $version = Rst_RecipeVersion::create([
                'recipe_id' => $recipeId,
                'version_no' => $versionNo,
                'is_active' => false, // New version is inactive by default
                'notes' => $data['notes'] ?? null,
                'effective_from' => $data['effective_from'] ?? null,
                'effective_to' => $data['effective_to'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Copy components from previous active version if requested
            if (! empty($data['copy_from_version_id'])) {
                $this->copyComponents($data['copy_from_version_id'], $version->id);
            }

            // Add new components if provided
            if (! empty($data['components'])) {
                $this->addComponentsToVersion($version->id, $data['components']);
            }

            return $version->fresh(['components']);
        });
    }

    /**
     * Delete version (only if not active)
     */
    public function deleteVersion(int $versionId): bool
    {
        return DB::connection('sccr_resto')->transaction(function () use ($versionId) {
            $version = Rst_RecipeVersion::findOrFail($versionId);

            if ($version->is_active) {
                throw new \Exception('Versi yang sedang aktif tidak bisa dihapus. Nonaktifkan dulu.');
            }

            $version->deleted_by = auth()->id();
            $version->save();

            return $version->delete();
        });
    }

    /**
     * Activate a version (deactivates other versions for the same recipe)
     */
    public function activateVersion(int $versionId): Rst_RecipeVersion
    {
        return DB::connection('sccr_resto')->transaction(function () use ($versionId) {
            $version = Rst_RecipeVersion::findOrFail($versionId);

            // Deactivate all other versions for this recipe
            Rst_RecipeVersion::where('recipe_id', $version->recipe_id)
                ->where('id', '!=', $versionId)
                ->update(['is_active' => false]);

            // Activate this version
            $version->update([
                'is_active' => true,
                'effective_from' => $version->effective_from ?? now(),
                'updated_by' => auth()->id(),
            ]);

            // Also ensure recipe is active
            $version->recipe->update([
                'is_active' => true,
                'updated_by' => auth()->id(),
            ]);

            return $version->fresh(['components', 'recipe']);
        });
    }

    /**
     * Get version history for a recipe
     */
    public function getVersionHistory(int $recipeId): array
    {
        $versions = Rst_RecipeVersion::where('recipe_id', $recipeId)
            ->withCount('components')
            ->orderBy('version_no', 'desc')
            ->get();

        return $versions->map(function ($version) {
            return [
                'id' => $version->id,
                'version_no' => $version->version_no,
                'is_active' => $version->is_active,
                'effective_from' => $version->effective_from,
                'effective_to' => $version->effective_to,
                'notes' => $version->notes,
                'components_count' => $version->components_count,
                'created_at' => $version->created_at,
            ];
        })->toArray();
    }

    /**
     * Add components to a version
     */
    private function addComponentsToVersion(int $versionId, array $components): void
    {
        $lineNo = 10;
        foreach ($components as $component) {
            Rst_RecipeComponent::create([
                'recipe_version_id' => $versionId,
                'line_no' => $lineNo,
                'component_item_id' => $component['item_id'],
                'component_kind' => 'item',
                'qty_standard' => $component['qty'],
                'uom_id' => $component['uom_id'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $lineNo += 10;
        }
    }

    /**
     * Copy components from one version to another
     */
    private function copyComponents(int $fromVersionId, int $toVersionId): void
    {
        $components = Rst_RecipeComponent::where('recipe_version_id', $fromVersionId)
            ->where(function ($q) {
                $q->where('component_kind', 'item')
                    ->orWhereNull('component_kind');
            })
            ->get();

        foreach ($components as $component) {
            Rst_RecipeComponent::create([
                'recipe_version_id' => $toVersionId,
                'line_no' => $component->line_no,
                'component_item_id' => $component->component_item_id,
                'component_kind' => 'item',
                'qty_standard' => $component->qty_standard,
                'uom_id' => $component->uom_id,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }
}
