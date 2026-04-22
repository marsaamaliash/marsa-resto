<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get first branch id
        $branchId = BranchSeeder::getFirstBranchId();

        $this->command->info("Using branch_id: {$branchId}");

        DB::connection('sccr_resto')->transaction(function () use ($branchId) {

            // 1. SEED CATEGORIES
            $categories = [
                ['name' => 'Bahan Baku', 'description' => 'Raw materials untuk dapur'],
                ['name' => 'Packaging', 'description' => 'Kardus, plastik, dll'],
                ['name' => 'Minuman', 'description' => 'Ready to drink atau bahan minuman'],
            ];
            foreach ($categories as $cat) {
                DB::connection('sccr_resto')->table('categories')->insert(array_merge($cat, [
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // 2. SEED UOMS
            $uoms = [
                ['name' => 'Kilogram', 'symbols' => 'Kg'],
                ['name' => 'Gram', 'symbols' => 'gr'],
                ['name' => 'Pieces', 'symbols' => 'Pcs'],
                ['name' => 'Liter', 'symbols' => 'L'],
            ];
            foreach ($uoms as $uom) {
                DB::connection('sccr_resto')->table('uoms')->insert(array_merge($uom, [
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // 3. SEED VENDORS
            $vendors = [
                [
                    'name' => 'PT Sumber Pangan',
                    'code' => 'VND-001',
                    'no_telp' => '021-123456',
                    'address' => 'Jl. Industri No. 10, Jakarta',
                ],
                [
                    'name' => 'CV Makmur Jaya',
                    'code' => 'VND-002',
                    'no_telp' => '0812-3456-789',
                    'address' => 'Pasar Induk Blok A, Semarang',
                ],
            ];
            foreach ($vendors as $vendor) {
                DB::connection('sccr_resto')->table('vendors')->insert(array_merge($vendor, [
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // 4. SEED ITEMS
            $catId = DB::connection('sccr_resto')->table('categories')->first()->id;
            $uomKg = DB::connection('sccr_resto')->table('uoms')->where('symbols', 'Kg')->first()->id;
            $uomPcs = DB::connection('sccr_resto')->table('uoms')->where('symbols', 'Pcs')->first()->id;

            $items = [
                [
                    'name' => 'Kecap Manis Bango 700ml',
                    'sku' => 'ITM-KCP-001',
                    'category_id' => $catId,
                    'uom_id' => $uomKg,
                    'min_stock' => 2.00,
                    'has_batch' => true,
                    'has_expiry' => true,
                ],
                [
                    'name' => 'Bimoli Minyak Goreng 2L/Pouch',
                    'sku' => 'ITM-MNG-001',
                    'category_id' => $catId,
                    'uom_id' => $uomPcs,
                    'min_stock' => 5.00,
                    'has_batch' => false,
                    'has_expiry' => true,
                ],
                [
                    'name' => 'Gulaku Gula Pasir 1kg',
                    'sku' => 'ITM-GLK-001',
                    'category_id' => $catId,
                    'uom_id' => $uomKg,
                    'min_stock' => 25.00,
                    'has_batch' => true,
                    'has_expiry' => false,
                ],
            ];

            foreach ($items as $item) {
                DB::connection('sccr_resto')->table('items')->insert(array_merge($item, [
                    'branch_id' => $branchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        });

        $this->command->info('MasterDataSeeder completed with branch_id.');
    }
}
