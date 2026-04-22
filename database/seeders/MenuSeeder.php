<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = BranchSeeder::getFirstBranchId();

        $menus = [
            [
                'branch_id' => $branchId,
                'recipe_id' => null,
                'name' => 'Nasi Goreng Spesial SCCR',
                'price' => 35000.00,
                'category' => 'Makanan Utama',
                'customer_segment' => 'Umum',
                'is_active' => true,
                'description' => 'Nasi goreng khas dengan bumbu rempah pilihan, dilengkapi telur mata sapi dan ayam suwir.',
                'image' => 'nasi-goreng-spesial.jpg',
                'stock' => 50,
                'discount' => 0.00,
                'slug' => Str::slug('Nasi Goreng Spesial SCCR'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'recipe_id' => null,
                'name' => 'Es Kopi Susu Aren',
                'price' => 18000.00,
                'category' => 'Minuman',
                'customer_segment' => 'Dewasa',
                'is_active' => true,
                'description' => 'Perpaduan espresso kental dengan susu segar dan manisnya gula aren asli.',
                'image' => 'kopi-susu-aren.jpg',
                'stock' => 100,
                'discount' => 2000.00,
                'slug' => Str::slug('Es Kopi Susu Aren'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'recipe_id' => null,
                'name' => 'Kentang Goreng Keju',
                'price' => 25000.00,
                'category' => 'Camilan',
                'customer_segment' => 'Anak-anak',
                'is_active' => true,
                'description' => 'Kentang goreng renyah dengan taburan bumbu keju gurih.',
                'image' => 'kentang-goreng.jpg',
                'stock' => 30,
                'discount' => 0.00,
                'slug' => Str::slug('Kentang Goreng Keju'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::connection('sccr_resto')->table('menus')->insert($menus);

        $this->command->info('MenuSeeder completed with branch_id.');
    }
}
