<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeComponent;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    /**
     * Create new recipe with initial version and components
     * Simplified: No approval workflow, no cost calculation
     */
    public function createRecipe(array $data): Rst_Recipe
    {
        return DB::connection('sccr_resto')->transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            if (empty($data['recipe_code'])) {
                $data['recipe_code'] = $this->generateRecipeCode();
            }

            // Deactivate any existing recipe for this menu
            if (! empty($data['menu_id'])) {
                Rst_Recipe::where('menu_id', $data['menu_id'])
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'updated_by' => auth()->id()]);
            }

            $recipe = Rst_Recipe::create($data);

            // Update the menu's recipe_id if this is a menu recipe
            if (! empty($data['menu_id'])) {
                \App\Models\Holdings\Resto\Pos\Rst_Menu::where('id', $data['menu_id'])
                    ->update(['recipe_id' => $recipe->id]);
            }

            // Create initial version (version 1) with is_active = true
            $version = Rst_RecipeVersion::create([
                'recipe_id' => $recipe->id,
                'version_no' => 1,
                'is_active' => true,
                'notes' => $data['version_notes'] ?? 'Initial version',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Add components if provided
            if (! empty($data['components'])) {
                $this->addComponentsToVersion($version->id, $data['components']);
            }

            return $recipe->fresh(['versions', 'versions.components']);
        });
    }

    /**
     * Deactivate recipe (soft delete is handled by SoftDeletes trait)
     */
    public function deactivateRecipe(int $id): Rst_Recipe
    {
        return DB::connection('sccr_resto')->transaction(function () use ($id) {
            $recipe = Rst_Recipe::findOrFail($id);

            $recipe->update([
                'is_active' => false,
                'updated_by' => auth()->id(),
            ]);

            // Also deactivate all versions
            Rst_RecipeVersion::where('recipe_id', $id)
                ->update(['is_active' => false]);

            return $recipe->fresh();
        });
    }

    /**
     * Get available kitchen items for recipe components
     * Filters: location.type = 'kitchen', qty_available > 0
     */
    public function getKitchenItems(?int $branchId = null, ?int $outletId = null): array
    {
        $query = DB::connection('sccr_resto')->table('stock_balances')
            ->join('items', 'stock_balances.item_id', '=', 'items.id')
            ->join('locations', 'stock_balances.location_id', '=', 'locations.id')
            ->join('uoms', 'stock_balances.uom_id', '=', 'uoms.id')
            ->where('locations.type', 'kitchen')
            ->where('stock_balances.qty_available', '>', 0)
            ->where('items.is_active', true);

        if ($branchId) {
            $query->where('locations.branch_id', $branchId);
        }

        if ($outletId) {
            $query->where('locations.outlet_id', $outletId);
        }

        return $query->select([
            'items.id as item_id',
            'items.name as item_name',
            'items.sku',
            'uoms.id as uom_id',
            'uoms.name as uom_name',
            'uoms.symbols as uom_symbol',
            'stock_balances.qty_available',
            'locations.id as location_id',
            'locations.name as location_name',
        ])
            ->orderBy('items.name')
            ->get()
            ->map(fn ($item) => [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'sku' => $item->sku,
                'uom_id' => $item->uom_id,
                'uom_name' => $item->uom_name,
                'uom_symbol' => $item->uom_symbol,
                'qty_available' => $item->qty_available,
                'location_id' => $item->location_id,
                'location_name' => $item->location_name,
            ])
            ->toArray();
    }

    /**
     * Validate that all component items are available in kitchen stock
     */
    public function validateComponents(array $components, ?int $branchId = null, ?int $outletId = null): array
    {
        $errors = [];
        $kitchenItems = $this->getKitchenItems($branchId, $outletId);
        $kitchenItemIds = array_column($kitchenItems, 'item_id');

        foreach ($components as $index => $component) {
            if (! in_array($component['item_id'], $kitchenItemIds)) {
                $errors[] = 'Komponen #'.($index + 1).': Item tidak tersedia di kitchen';
            }

            if (empty($component['qty']) || $component['qty'] <= 0) {
                $errors[] = 'Komponen #'.($index + 1).': Qty harus lebih dari 0';
            }
        }

        return $errors;
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

    private function generateRecipeCode(): string
    {
        $lastRecipe = Rst_Recipe::orderBy('id', 'desc')->first();
        $nextNumber = $lastRecipe ? ((int) str_replace('RCP-', '', $lastRecipe->recipe_code)) + 1 : 1;

        return 'RCP-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
