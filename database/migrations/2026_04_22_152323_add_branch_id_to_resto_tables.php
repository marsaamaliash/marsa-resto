<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'categories',
        'vendors',
        'menus',
        'movements',
        'movement_items',
        'stock_balances',
        'stock_mutations',
        'orders',
        'order_items',
        'purchase_orders',
        'purchase_order_items',
        'purchase_requests',
        'purchase_request_items',
        'goods_receipts',
        'goods_receipt_items',
        'stock_opnames',
        'stock_opname_items',
        'stock_opname_freezes',
        'stock_opname_adjustments',
        'employees',
        'payments',
        'failed_order_items',
        'employee_lunch_transactions',
        'request_activities',
        'direct_orders',
        'direct_order_items',
        'uom_conversions',
        'stock_repacks',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::connection('sccr_resto')->hasTable($tableName)) {
                continue;
            }

            Schema::connection('sccr_resto')->table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::connection('sccr_resto')->hasColumn($tableName, 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                    $table->index('branch_id');
                }
            });
        }

        // Set default branch_id untuk data existing
        $this->setDefaultBranchId();
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::connection('sccr_resto')->hasTable($tableName)) {
                continue;
            }

            Schema::connection('sccr_resto')->table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::connection('sccr_resto')->hasColumn($tableName, 'branch_id')) {
                    $table->dropIndex(['branch_id']);
                    $table->dropColumn('branch_id');
                }
            });
        }
    }

    protected function setDefaultBranchId(): void
    {
        // Cek apakah sudah ada branch (dari seeder)
        $firstBranch = \DB::connection('sccr_resto')->table('branches')->first();

        if (! $firstBranch) {
            // Jika belum ada branch, skip update (branch_id akan null)
            // User harus jalankan seeder untuk membuat branch
            return;
        }

        $branchId = $firstBranch->id;

        // Update semua tabel dengan branch_id dari branch pertama (dari seeder)
        foreach ($this->tables as $tableName) {
            if (! Schema::connection('sccr_resto')->hasTable($tableName)) {
                continue;
            }

            \DB::connection('sccr_resto')->table($tableName)
                ->whereNull('branch_id')
                ->update(['branch_id' => $branchId]);
        }
    }
};
