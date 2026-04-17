<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionCostSummary;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionMaterialIssueLine;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOrder;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionOutputLine;
use App\Models\Holdings\Resto\Produksi\Rst_ProductionWasteLine;
use Illuminate\Support\Facades\DB;

class ProductionConsumeService
{
    public function recordWaste(int $orderId, array $data): Rst_ProductionWasteLine
    {
        return DB::connection('sccr_resto')->transaction(function () use ($orderId, $data) {
            $order = Rst_ProductionOrder::findOrFail($orderId);

            if (! in_array($order->status, ['issued', 'in_progress'])) {
                throw new \Exception('Waste recording hanya bisa dilakukan pada status issued/in_progress.');
            }

            $maxLineNo = Rst_ProductionWasteLine::where('prod_order_id', $orderId)
                ->max('line_no') ?? 0;

            $wasteLine = Rst_ProductionWasteLine::create([
                'prod_order_id' => $orderId,
                'line_no' => $maxLineNo + 10,
                'waste_stage' => $data['waste_stage'] ?? 'production',
                'waste_type' => $data['waste_type'] ?? 'normal',
                'item_id' => $data['item_id'],
                'qty_waste' => $data['qty_waste'],
                'uom_id' => $data['uom_id'],
                'base_qty_waste' => $data['qty_waste'],
                'actual_unit_cost' => $data['actual_unit_cost'] ?? 0,
                'actual_total_cost' => ($data['actual_unit_cost'] ?? 0) * $data['qty_waste'],
                'charge_mode' => $data['charge_mode'] ?? 'absorbed',
                'reason_code' => $data['reason_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            if (($data['charge_mode'] ?? 'absorbed') === 'deduct_stock') {
                $this->deductWasteFromStock($data['item_id'], $order->issue_location_id, (float) $data['qty_waste'], $order->prod_no);
            }

            return $wasteLine->fresh();
        });
    }

    public function completeProduction(int $orderId): Rst_ProductionOrder
    {
        return DB::connection('sccr_resto')->transaction(function () use ($orderId) {
            $order = Rst_ProductionOrder::findOrFail($orderId);

            if ($order->status !== 'in_progress') {
                throw new \Exception('Hanya production order berstatus in_progress yang bisa di-complete.');
            }

            $this->generateCostSummary($orderId);

            $unpostedOutputs = Rst_ProductionOutputLine::where('prod_order_id', $orderId)
                ->where('posted_to_inventory', false)
                ->where('qc_status', '!=', 'rejected')
                ->get();

            foreach ($unpostedOutputs as $outputLine) {
                $executionService = app(ProductionExecutionService::class);
                $executionService->postOutputToInventory($outputLine->id);
            }

            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            return $order->fresh();
        });
    }

    public function cancelProduction(int $orderId, string $reason = ''): Rst_ProductionOrder
    {
        return DB::connection('sccr_resto')->transaction(function () use ($orderId, $reason) {
            $order = Rst_ProductionOrder::findOrFail($orderId);

            if (! in_array($order->status, ['draft', 'issued', 'in_progress'])) {
                throw new \Exception('Hanya production order berstatus draft/issued/in_progress yang bisa dibatalkan.');
            }

            $order->update([
                'status' => 'cancelled',
                'notes' => trim(($order->notes ?? '')."\n[CANCELLED] ".now()->format('Y-m-d H:i').($reason ? ': '.$reason : '')),
                'updated_by' => auth()->id(),
            ]);

            return $order->fresh();
        });
    }

    public function generateCostSummary(int $orderId): Rst_ProductionCostSummary
    {
        $order = Rst_ProductionOrder::findOrFail($orderId);

        $materialCostTotal = Rst_ProductionMaterialIssueLine::where('prod_order_id', $orderId)
            ->sum('actual_total_cost');

        $wasteCostTotal = Rst_ProductionWasteLine::where('prod_order_id', $orderId)
            ->where('charge_mode', 'absorbed')
            ->sum('actual_total_cost');

        $normalLossCost = Rst_ProductionWasteLine::where('prod_order_id', $orderId)
            ->where('waste_type', 'normal')
            ->sum('actual_total_cost');

        $abnormalWasteCost = Rst_ProductionWasteLine::where('prod_order_id', $orderId)
            ->where('waste_type', 'abnormal')
            ->sum('actual_total_cost');

        $totalInputCost = $materialCostTotal + $wasteCostTotal;
        $totalOutputCost = (float) ($order->actual_output_qty ?? 0) > 0
            ? $totalInputCost
            : 0;

        $costPerUnit = ($order->actual_output_qty ?? 0) > 0
            ? $totalInputCost / (float) $order->actual_output_qty
            : 0;

        $existingSummary = Rst_ProductionCostSummary::where('prod_order_id', $orderId)->first();

        $summaryData = [
            'material_cost_total' => round($materialCostTotal, 4),
            'packaging_cost_total' => 0,
            'labor_absorbed_total' => 0,
            'overhead_absorbed_total' => 0,
            'normal_loss_cost_total' => round($normalLossCost, 4),
            'abnormal_waste_cost_total' => round($abnormalWasteCost, 4),
            'total_input_cost' => round($totalInputCost, 4),
            'total_output_cost' => round($totalOutputCost, 4),
            'yield_variance_cost' => round($totalInputCost - $totalOutputCost, 4),
            'cost_per_output_unit' => round($costPerUnit, 4),
            'computed_at' => now(),
            'notes' => 'Auto-generated cost summary',
            'updated_by' => auth()->id(),
        ];

        if ($existingSummary) {
            $existingSummary->update($summaryData);

            return $existingSummary->fresh();
        }

        $summaryData['prod_order_id'] = $orderId;
        $summaryData['created_by'] = auth()->id();

        return Rst_ProductionCostSummary::create($summaryData);
    }

    public function getCostSummary(int $orderId): ?Rst_ProductionCostSummary
    {
        return Rst_ProductionCostSummary::where('prod_order_id', $orderId)->first();
    }

    public function getWasteLines(int $orderId)
    {
        return Rst_ProductionWasteLine::where('prod_order_id', $orderId)
            ->with(['item', 'uom'])
            ->orderBy('line_no')
            ->get();
    }

    private function deductWasteFromStock(int $itemId, int $locationId, float $qty, string $referenceNo): void
    {
        $balance = Rst_StockBalance::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if (! $balance || $balance->qty_available < $qty) {
            return;
        }

        $qtyBefore = $balance->qty_available;
        $balance->decrement('qty_available', $qty);

        Rst_StockMutation::create([
            'item_id' => $itemId,
            'location_id' => $locationId,
            'uom_id' => $balance->uom_id,
            'type' => 'waste',
            'reference_number' => $referenceNo,
            'qty' => $qty,
            'qty_before' => $qtyBefore,
            'qty_after' => $balance->fresh()->qty_available,
            'user_id' => auth()->id(),
            'notes' => 'Production waste: '.$referenceNo,
        ]);
    }
}
