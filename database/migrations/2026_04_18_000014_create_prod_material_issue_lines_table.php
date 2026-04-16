<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_material_issue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_order_id')->constrained('prod_orders')->onDelete('cascade');
            $table->integer('line_no');
            $table->unsignedBigInteger('plan_line_id')->nullable();

            $table->string('issue_type', 20)->default('production_issue');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('issue_location_id')->constrained('locations')->onDelete('restrict');

            $table->decimal('qty_issued', 20, 6)->default(0);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');
            $table->decimal('base_qty_issued', 20, 6)->default(0);

            $table->decimal('actual_unit_cost', 20, 4)->default(0);
            $table->decimal('actual_total_cost', 20, 4)->default(0);
            $table->string('costing_method_used', 20)->default('batch_actual');

            $table->string('reason_code', 30)->nullable();
            $table->string('notes')->nullable();

            $table->timestamp('issued_at');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['prod_order_id', 'line_no'], 'uq_prod_issue_lines_line');
            $table->index('prod_order_id', 'idx_prod_issue_lines_order');
            $table->index('plan_line_id', 'idx_prod_issue_lines_plan');
            $table->index('item_id', 'idx_prod_issue_lines_item');
            $table->index('issue_location_id', 'idx_prod_issue_lines_loc');
            $table->index('issue_type', 'idx_prod_issue_lines_type');
            $table->index('reason_code', 'idx_prod_issue_lines_reason');
            $table->index('issued_at', 'idx_prod_issue_lines_issued_at');
            $table->index('uom_id', 'idx_prod_issue_lines_uom');

            $table->foreign('plan_line_id')->references('id')->on('prod_order_component_plans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_material_issue_lines');
    }
};
