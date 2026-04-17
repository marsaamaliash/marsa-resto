<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Resep\Rst_RecipeCostSnapshot;
use App\Models\Holdings\Resto\Resep\Rst_RecipeOutput;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use Illuminate\Support\Facades\DB;

class RecipeCostService
{
    public function calculateAndSnapshot(int $versionId): Rst_RecipeCostSnapshot
    {
        $version = Rst_RecipeVersion::findOrFail($versionId);

        $bomService = app(RecipeBomService::class);
        $materialCost = $bomService->calculateMaterialCost($versionId);

        $outputs = Rst_RecipeOutput::where('recipe_version_id', $versionId)
            ->orderBy('line_no')
            ->get();

        $totalOutputQty = $outputs->sum(function ($output) {
            return (float) $output->planned_qty;
        });

        $costPerUnit = $totalOutputQty > 0 ? $materialCost / $totalOutputQty : $materialCost;

        return DB::connection('sccr_resto')->transaction(function () use ($versionId, $materialCost, $costPerUnit) {
            return Rst_RecipeCostSnapshot::create([
                'recipe_version_id' => $versionId,
                'snapshot_date' => now()->toDateString(),
                'cost_basis' => 'standard',
                'material_cost' => round($materialCost, 4),
                'packaging_cost' => 0,
                'overhead_cost' => 0,
                'labor_cost' => 0,
                'total_batch_cost' => round($materialCost, 4),
                'cost_per_output_unit' => round($costPerUnit, 4),
                'notes' => 'Auto-calculated from BOM',
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function getLatestSnapshot(int $versionId): ?Rst_RecipeCostSnapshot
    {
        return Rst_RecipeCostSnapshot::where('recipe_version_id', $versionId)
            ->orderBy('snapshot_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getCostHistory(int $versionId, int $limit = 10)
    {
        return Rst_RecipeCostSnapshot::where('recipe_version_id', $versionId)
            ->orderBy('snapshot_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }
}
