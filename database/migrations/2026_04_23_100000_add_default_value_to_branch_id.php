<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

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
        'prod_orders',
        'items',
        'locations',
        'inv_inventory_batches',
        'rec_recipes',
        'uoms',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::connection($this->connection)->hasTable($table)) {
                continue;
            }

            if (! Schema::connection($this->connection)->hasColumn($table, 'branch_id')) {
                continue;
            }

            DB::connection($this->connection)
                ->table($table)
                ->whereNull('branch_id')
                ->update(['branch_id' => 1]);

            DB::connection($this->connection)
                ->statement("ALTER TABLE `{$table}` ALTER COLUMN `branch_id` SET DEFAULT 1");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::connection($this->connection)->hasTable($table)) {
                continue;
            }

            if (! Schema::connection($this->connection)->hasColumn($table, 'branch_id')) {
                continue;
            }

            DB::connection($this->connection)
                ->statement("ALTER TABLE `{$table}` ALTER COLUMN `branch_id` DROP DEFAULT");
        }
    }
};
