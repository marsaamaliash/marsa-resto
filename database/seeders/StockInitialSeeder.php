<?php

namespace Database\Seeders;

use App\Services\Resto\StockMutationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockInitialSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = BranchSeeder::getFirstBranchId();

        $conn = DB::connection('sccr_resto');

        $locations = $conn->table('locations')->where('branch_id', $branchId)->pluck('id');
        $items = $conn->table('items')->where('branch_id', $branchId)->get();

        if ($items->isEmpty() || $locations->isEmpty()) {
            $this->command->error('Items atau Locations kosong! Jalankan seeder master dulu.');

            return;
        }

        $stockData = [
            1 => [1, 2, 3],
            2 => [1, 2],
            3 => [],
        ];

        foreach ($locations as $locationId) {
            $itemIds = $stockData[$locationId] ?? [1];

            foreach ($itemIds as $itemId) {
                $item = $items->firstWhere('id', $itemId);
                if (! $item) {
                    continue;
                }

                $qty = rand(10, 100);

                StockMutationService::addMutation(
                    $itemId,
                    $locationId,
                    $item->uom_id,
                    $qty,
                    'in',
                    'Initial stock seeder'
                );
            }

            $this->command->info("Location {$locationId} seeded.");
        }

        $this->command->info('Stock initial seeding completed with branch_id.');
    }
}
