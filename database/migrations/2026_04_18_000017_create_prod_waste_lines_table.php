<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_waste_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_order_id')->constrained('prod_orders')->onDelete('cascade');
            $table->integer('line_no');

            $table->string('waste_stage', 20)->default('main');
            $table->string('waste_type', 30);

            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->unsignedBigInteger('inventory_batch_id')->nullable();

            $table->decimal('qty_waste', 20, 6)->default(0);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');
            $table->decimal('base_qty_waste', 20, 6)->default(0);

            $table->decimal('actual_unit_cost', 20, 4)->default(0);
            $table->decimal('actual_total_cost', 20, 4)->default(0);

            $table->string('charge_mode', 20)->default('to_waste_expense');
            $table->string('reason_code', 30)->nullable();
            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['prod_order_id', 'line_no'], 'uq_prod_waste_lines_line');
            $table->index('prod_order_id', 'idx_prod_waste_lines_order');
            $table->index('waste_stage', 'idx_prod_waste_lines_stage');
            $table->index('waste_type', 'idx_prod_waste_lines_type');
            $table->index('item_id', 'idx_prod_waste_lines_item');
            $table->index('uom_id', 'idx_prod_waste_lines_uom');
            $table->index('reason_code', 'idx_prod_waste_lines_reason');
            $table->index('charge_mode', 'idx_prod_waste_lines_charge_mode');

            $table->foreign('inventory_batch_id')->references('id')->on('inv_inventory_batches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_waste_lines');
    }
};
