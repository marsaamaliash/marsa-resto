<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_output_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_order_id')->constrained('prod_orders')->onDelete('cascade');
            $table->integer('line_no');

            $table->string('output_type', 20)->default('main');
            $table->foreignId('output_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('output_location_id')->constrained('locations')->onDelete('restrict');

            $table->decimal('qty_output', 20, 6)->default(0);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

            $table->unsignedBigInteger('inventory_batch_id')->nullable();
            $table->decimal('actual_total_cost_allocated', 20, 4)->default(0);
            $table->decimal('actual_unit_cost', 20, 4)->default(0);

            $table->string('qc_status', 20)->default('pending');
            $table->boolean('posted_to_inventory')->default(false);

            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['prod_order_id', 'line_no'], 'uq_prod_output_lines_line');
            $table->index('prod_order_id', 'idx_prod_output_lines_order');
            $table->index('output_type', 'idx_prod_output_lines_type');
            $table->index('output_item_id', 'idx_prod_output_lines_item');
            $table->index('output_location_id', 'idx_prod_output_lines_loc');
            $table->index('uom_id', 'idx_prod_output_lines_uom');
            $table->index('qc_status', 'idx_prod_output_lines_qc');

            $table->foreign('inventory_batch_id')->references('id')->on('inv_inventory_batches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_output_lines');
    }
};
