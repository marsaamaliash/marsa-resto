<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Produksi\Rst_ProductionOrder;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOrderComponentPlan;
use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeComponent;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use Illuminate\Support\Facades\DB;

class ProductionOrderService
{
    public function createFromRecipe(int $recipeId, int $versionId, array $data): Rst_ProductionOrder
    {
        return DB::connection('sccr_resto')->transaction(function () use ($recipeId, $versionId, $data) {
            $recipe = Rst_Recipe::findOrFail($recipeId);
            $version = Rst_RecipeVersion::findOrFail($versionId);

            if ($version->recipe_id !== $recipeId) {
                throw new \Exception('Versi tidak sesuai dengan resep yang dipilih.');
            }

            if (! $version->is_active) {
                throw new \Exception('Hanya versi aktif yang bisa diproduksi.');
            }

            $prodNo = $this->generateProdNo();

            $order = Rst_ProductionOrder::create([
                'holding_id' => $data['holding_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'outlet_id' => $data['outlet_id'] ?? null,
                'prod_no' => $prodNo,
                'prod_type' => $data['prod_type'] ?? 'standard',
                'recipe_id' => $recipeId,
                'recipe_version_id' => $versionId,
                'issue_location_id' => $data['issue_location_id'],
                'output_location_id' => $data['output_location_id'],
                'planned_output_qty' => $data['planned_output_qty'],
                'actual_output_qty' => 0,
                'output_uom_id' => $data['output_uom_id'] ?? $recipe->default_uom_id,
                'status' => 'draft',
                'approval_status' => 'draft',
                'business_date' => $data['business_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->generateComponentPlans($order, $version, (float) $data['planned_output_qty']);

            return $order->fresh(['recipe', 'recipeVersion', 'componentPlans', 'issueLocation', 'outputLocation', 'outputUom']);
        });
    }

    public function updateStatus(int $orderId, string $status): Rst_ProductionOrder
    {
        $order = Rst_ProductionOrder::findOrFail($orderId);

        $validTransitions = [
            'draft' => ['issued'],
            'issued' => ['in_progress'],
            'in_progress' => ['completed', 'cancelled'],
        ];

        if (! isset($validTransitions[$order->status]) || ! in_array($status, $validTransitions[$order->status])) {
            throw new \Exception("Tidak bisa mengubah status dari '{$order->status}' ke '{$status}'.");
        }

        $order->update([
            'status' => $status,
            'updated_by' => auth()->id(),
        ]);

        if ($status === 'issued') {
            $order->update(['started_at' => now()]);
        }

        if ($status === 'completed') {
            $order->update(['completed_at' => now()]);
        }

        return $order->fresh();
    }

    public function deleteOrder(int $orderId): bool
    {
        $order = Rst_ProductionOrder::findOrFail($orderId);

        if ($order->status !== 'draft') {
            throw new \Exception('Hanya production order berstatus draft yang bisa dihapus.');
        }

        $order->deleted_by = auth()->id();
        $order->save();

        return $order->delete();
    }

    private function generateComponentPlans(Rst_ProductionOrder $order, Rst_RecipeVersion $version, float $plannedOutputQty): void
    {
        $batchSize = (float) $version->batch_size_qty;
        $numberOfBatches = $batchSize > 0 ? ceil($plannedOutputQty / $batchSize) : 1;

        $components = Rst_RecipeComponent::where('recipe_version_id', $version->id)
            ->orderBy('line_no')
            ->get();

        $lineNo = 10;
        foreach ($components as $component) {
            $standardCost = 0;
            if ($component->component_kind === 'item' && $component->componentItem) {
                $standardCost = (float) ($component->componentItem->cost_standard ?? 0);
            } elseif ($component->component_kind === 'recipe') {
                $standardCost = app(RecipeBomService::class)->getRecipeCost($component->component_recipe_id);
            }

            $effectiveQtyPerBatch = (float) $component->qty_standard * (1 + (float) $component->wastage_pct_standard / 100);

            Rst_ProductionOrderComponentPlan::create([
                'prod_order_id' => $order->id,
                'line_no' => $lineNo,
                'component_kind' => $component->component_kind,
                'component_item_id' => $component->component_kind === 'item' ? $component->component_item_id : null,
                'component_recipe_id' => $component->component_kind === 'recipe' ? $component->component_recipe_id : null,
                'stage_code' => $component->stage_code ?? 'main',
                'qty_standard_per_batch' => $effectiveQtyPerBatch,
                'planned_total_qty' => $effectiveQtyPerBatch * $numberOfBatches,
                'uom_id' => $component->uom_id,
                'standard_unit_cost' => $standardCost,
                'standard_total_cost' => $standardCost * $effectiveQtyPerBatch * $numberOfBatches,
                'wastage_pct_standard' => $component->wastage_pct_standard,
                'notes' => $component->notes,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $lineNo += 10;
        }
    }

    private function generateProdNo(): string
    {
        return 'PRD-'.now()->format('Ymd').'-'.strtoupper(str()->random(4));
    }
}
