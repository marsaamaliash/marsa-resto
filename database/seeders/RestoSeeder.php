<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RestoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('Seeding Resto with Multi-Branch Support');
        $this->command->info('========================================');

        $this->command->info('Seeding branches...');
        $this->call(BranchSeeder::class);

        $this->command->info('Seeding user_branches...');
        $this->call(UserBranchSeeder::class);

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

        $this->command->info('========================================');
        $this->command->info('RestoSeeder completed successfully!');
        $this->command->info('3 Branches created: Jakarta Pusat, Bandung, Surabaya');
        $this->command->info('All data assigned to first branch (Jakarta Pusat)');
        $this->command->info('========================================');
    }
}
