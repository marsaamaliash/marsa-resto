<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_cost_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_order_id')->constrained('prod_orders')->onDelete('cascade')->unique('uq_prod_cost_summaries_order');
            $table->unsignedBigInteger('cost_center_id')->nullable();

            $table->decimal('material_cost_total', 20, 4)->default(0);
            $table->decimal('packaging_cost_total', 20, 4)->default(0);
            $table->decimal('labor_absorbed_total', 20, 4)->default(0);
            $table->decimal('overhead_absorbed_total', 20, 4)->default(0);

            $table->decimal('normal_loss_cost_total', 20, 4)->default(0);
            $table->decimal('abnormal_waste_cost_total', 20, 4)->default(0);

            $table->decimal('total_input_cost', 20, 4)->default(0);
            $table->decimal('total_output_cost', 20, 4)->default(0);
            $table->decimal('yield_variance_cost', 20, 4)->default(0);
            $table->decimal('cost_per_output_unit', 20, 4)->default(0);

            $table->timestamp('computed_at');
            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('cost_center_id', 'idx_prod_cost_summaries_cost_center');
            $table->index('computed_at', 'idx_prod_cost_summaries_computed');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_cost_summaries');
    }
};
