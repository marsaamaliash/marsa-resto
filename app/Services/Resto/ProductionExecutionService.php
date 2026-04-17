<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionMaterialIssueBatch;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionMaterialIssueLine;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOrder;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOrderComponentPlan;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOutputLine;
use Illuminate\Support\Facades\DB;

class ProductionExecutionService
{
    public function issueMaterial(int $orderId, int $planLineId, array $data): Rst_ProductionMaterialIssueLine
    {
        return DB::connection('sccr_resto')->transaction(function () use ($orderId, $planLineId, $data) {
            $order = Rst_ProductionOrder::findOrFail($orderId);

            if (! in_array($order->status, ['draft', 'issued', 'in_progress'])) {
                throw new \Exception('Material issue hanya bisa dilakukan pada status draft/issued/in_progress.');
            }

            $planLine = Rst_ProductionOrderComponentPlan::where('prod_order_id', $orderId)
                ->where('id', $planLineId)
                ->firstOrFail();

            $itemId = $planLine->component_kind === 'item' ? $planLine->component_item_id : $planLine->component_recipe_id;
            if ($planLine->component_kind === 'recipe') {
                $recipe = \App\Models\Holdings\Resto\Resep\Rst_Recipe::find($planLine->component_recipe_id);
                $itemId = $recipe?->output_item_id;
            }

            $maxLineNo = Rst_ProductionMaterialIssueLine::where('prod_order_id', $orderId)
                ->max('line_no') ?? 0;

            $issueLine = Rst_ProductionMaterialIssueLine::create([
                'prod_order_id' => $orderId,
                'line_no' => $maxLineNo + 10,
                'plan_line_id' => $planLineId,
                'issue_type' => 'standard',
                'item_id' => $itemId ?? $planLine->component_item_id,
                'issue_location_id' => $data['issue_location_id'] ?? $order->issue_location_id,
                'qty_issued' => $data['qty_issued'],
                'uom_id' => $data['uom_id'] ?? $planLine->uom_id,
                'base_qty_issued' => $data['qty_issued'],
                'actual_unit_cost' => $data['actual_unit_cost'] ?? $planLine->standard_unit_cost,
                'actual_total_cost' => ($data['actual_unit_cost'] ?? $planLine->standard_unit_cost) * $data['qty_issued'],
                'costing_method_used' => 'standard',
                'notes' => $data['notes'] ?? null,
                'issued_at' => now(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            if (! empty($data['batches'])) {
                foreach ($data['batches'] as $batchData) {
                    Rst_ProductionMaterialIssueBatch::create([
                        'issue_line_id' => $issueLine->id,
                        'inventory_batch_id' => $batchData['inventory_batch_id'] ?? null,
                        'batch_no' => $batchData['batch_no'] ?? null,
                        'expiry_date' => $batchData['expiry_date'] ?? null,
                        'qty_issued_base' => $batchData['qty_issued_base'],
                        'unit_cost' => $batchData['unit_cost'] ?? 0,
                        'total_cost' => ($batchData['unit_cost'] ?? 0) * $batchData['qty_issued_base'],
                        'issue_sequence' => $batchData['issue_sequence'] ?? 1,
                    ]);
                }
            }

            $this->deductStock(
                $itemId ?? $planLine->component_item_id,
                $data['issue_location_id'] ?? $order->issue_location_id,
                (float) $data['qty_issued'],
                $order->prod_no
            );

            if ($order->status === 'draft') {
                $order->update(['status' => 'issued', 'started_at' => now(), 'updated_by' => auth()->id()]);
            }

            return $issueLine->fresh();
        });
    }

    public function recordOutput(int $orderId, array $data): Rst_ProductionOutputLine
    {
        return DB::connection('sccr_resto')->transaction(function () use ($orderId, $data) {
            $order = Rst_ProductionOrder::findOrFail($orderId);

            if (! in_array($order->status, ['issued', 'in_progress'])) {
                throw new \Exception('Output recording hanya bisa dilakukan pada status issued/in_progress.');
            }

            $maxLineNo = Rst_ProductionOutputLine::where('prod_order_id', $orderId)
                ->max('line_no') ?? 0;

            $totalCostInput = Rst_ProductionMaterialIssueLine::where('prod_order_id', $orderId)
                ->sum('actual_total_cost');
            $totalOutputSoFar = Rst_ProductionOutputLine::where('prod_order_id', $orderId)
                ->sum('qty_output');
            $remainingCost = $totalOutputSoFar > 0 ? 0 : $totalCostInput;
            $unitCost = ($data['qty_output'] ?? 0) > 0 ? $remainingCost / $data['qty_output'] : 0;

            $outputLine = Rst_ProductionOutputLine::create([
                'prod_order_id' => $orderId,
                'line_no' => $maxLineNo + 10,
                'output_type' => $data['output_type'] ?? 'main',
                'output_item_id' => $data['output_item_id'],
                'output_location_id' => $data['output_location_id'] ?? $order->output_location_id,
                'qty_output' => $data['qty_output'],
                'uom_id' => $data['uom_id'] ?? $order->output_uom_id,
                'actual_total_cost_allocated' => $data['actual_total_cost_allocated'] ?? 0,
                'actual_unit_cost' => $unitCost,
                'qc_status' => $data['qc_status'] ?? 'pending',
                'posted_to_inventory' => false,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $order->update([
                'actual_output_qty' => ($order->actual_output_qty ?? 0) + (float) $data['qty_output'],
                'updated_by' => auth()->id(),
            ]);

            if (isset($data['post_to_inventory']) && $data['post_to_inventory']) {
                $this->postToInventory($outputLine, $order);
            }

            return $outputLine->fresh();
        });
    }

    public function postOutputToInventory(int $outputLineId): Rst_ProductionOutputLine
    {
        return DB::connection('sccr_resto')->transaction(function () use ($outputLineId) {
            $outputLine = Rst_ProductionOutputLine::findOrFail($outputLineId);

            if ($outputLine->posted_to_inventory) {
                throw new \Exception('Output ini sudah di-post ke inventory.');
            }

            $order = Rst_ProductionOrder::findOrFail($outputLine->prod_order_id);

            $this->postToInventory($outputLine, $order);

            $outputLine->update([
                'posted_to_inventory' => true,
                'updated_by' => auth()->id(),
            ]);

            return $outputLine->fresh();
        });
    }

    private function postToInventory(Rst_ProductionOutputLine $outputLine, Rst_ProductionOrder $order): void
    {
        $balance = Rst_StockBalance::firstOrNew([
            'item_id' => $outputLine->output_item_id,
            'location_id' => $outputLine->output_location_id,
        ]);

        $qtyBefore = $balance->exists ? $balance->qty_available : 0;
        $balance->uom_id = $outputLine->uom_id;
        $balance->qty_available = ($balance->qty_available ?? 0) + (float) $outputLine->qty_output;
        $balance->save();

        Rst_StockMutation::create([
            'item_id' => $outputLine->output_item_id,
            'location_id' => $outputLine->output_location_id,
            'uom_id' => $outputLine->uom_id,
            'type' => 'production_in',
            'reference_number' => $order->prod_no,
            'qty' => (float) $outputLine->qty_output,
            'qty_before' => $qtyBefore,
            'qty_after' => $balance->fresh()->qty_available,
            'user_id' => auth()->id(),
            'notes' => 'Production output: '.$order->prod_no,
        ]);
    }

    private function deductStock(int $itemId, int $locationId, float $qty, string $referenceNo): void
    {
        $balance = Rst_StockBalance::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (! $balance || $balance->qty_available < $qty) {
            $itemName = Rst_MasterItem::find($itemId)?->name ?? 'Item';
            throw new \Exception("Stok {$itemName} tidak mencukupi di lokasi ini.");
        }

        $qtyBefore = $balance->qty_available;
        $balance->decrement('qty_available', $qty);

        Rst_StockMutation::create([
            'item_id' => $itemId,
            'location_id' => $locationId,
            'uom_id' => $balance->uom_id,
            'type' => 'production_out',
            'reference_number' => $referenceNo,
            'qty' => $qty,
            'qty_before' => $qtyBefore,
            'qty_after' => $balance->fresh()->qty_available,
            'user_id' => auth()->id(),
            'notes' => 'Material issue for production: '.$referenceNo,
        ]);
    }
}
