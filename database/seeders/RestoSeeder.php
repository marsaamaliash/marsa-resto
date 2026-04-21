<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RestoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Resto master data...');
        $this->call(MasterDataSeeder::class);

        $this->command->info('Seeding Resto locations...');
        $this->call(LokasiSeeder::class);

        $this->command->info('Seeding Resto meja...');
        $this->call(MejaSeeder::class);

        $this->command->info('Seeding Resto initial stock...');
        $this->call(StockInitialSeeder::class);

        $this->command->info('Seeding Resto menus...');
        $this->call(MenuSeeder::class);

        $this->command->info('Seeding Resto employees...');
        $this->call(RestoEmployeeSeeder::class);

        $this->command->info('RestoSeeder completed.');
    }
}
