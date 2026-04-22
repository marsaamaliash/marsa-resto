<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserBranchSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding user_branches...');

        // Get first branch id
        $firstBranchId = DB::connection('sccr_resto')->table('branches')->first()?->id;

        if (! $firstBranchId) {
            $this->command->error('No branches found. Run BranchSeeder first.');

            return;
        }

        // Get first auth_user id
        $firstUserId = DB::connection('mysql')->table('auth_users')->first()?->id;

        if (! $firstUserId) {
            $this->command->error('No auth_users found.');

            return;
        }

        // Assign first user to first branch as default
        DB::connection('sccr_resto')->table('user_branches')->insertOrIgnore([
            'auth_user_id' => $firstUserId,
            'branch_id' => $firstBranchId,
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("User {$firstUserId} assigned to branch {$firstBranchId} as default.");
    }
}
