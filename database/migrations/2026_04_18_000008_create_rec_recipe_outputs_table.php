<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipe_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_version_id')->constrained('rec_recipe_versions')->onDelete('cascade');
            $table->integer('line_no');

            $table->string('output_type', 20)->default('main');
            $table->foreignId('output_item_id')->constrained('items')->onDelete('restrict');

            $table->decimal('planned_qty', 20, 6);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

            $table->decimal('cost_allocation_pct', 10, 4)->nullable();
            $table->boolean('is_inventory_item')->default(true);

            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['recipe_version_id', 'line_no'], 'uq_rec_recipe_outputs_line');
            $table->index('recipe_version_id', 'idx_rec_recipe_outputs_recipe_ver');
            $table->index('output_type', 'idx_rec_recipe_outputs_type');
            $table->index('output_item_id', 'idx_rec_recipe_outputs_item');
            $table->index('uom_id', 'idx_rec_recipe_outputs_uom');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipe_outputs');
    }
};
