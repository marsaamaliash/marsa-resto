<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_order_component_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prod_order_id')->constrained('prod_orders')->onDelete('cascade');
            $table->integer('line_no');

            $table->string('component_kind', 20);
            $table->unsignedBigInteger('component_item_id')->nullable();
            $table->unsignedBigInteger('component_recipe_id')->nullable();

            $table->string('stage_code', 20)->default('main');

            $table->decimal('qty_standard_per_batch', 20, 6)->default(0);
            $table->decimal('planned_total_qty', 20, 6)->default(0);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

            $table->decimal('standard_unit_cost', 20, 4)->nullable();
            $table->decimal('standard_total_cost', 20, 4)->nullable();
            $table->decimal('wastage_pct_standard', 10, 4)->default(0);

            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['prod_order_id', 'line_no'], 'uq_prod_component_plans_line');
            $table->index('prod_order_id', 'idx_prod_component_plans_order');
            $table->index('component_item_id', 'idx_prod_component_plans_item');
            $table->index('component_recipe_id', 'idx_prod_component_plans_recipe');
            $table->index('stage_code', 'idx_prod_component_plans_stage');
            $table->index('uom_id', 'idx_prod_component_plans_uom');

            $table->foreign('component_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('component_recipe_id')->references('id')->on('rec_recipes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_order_component_plans');
    }
};
