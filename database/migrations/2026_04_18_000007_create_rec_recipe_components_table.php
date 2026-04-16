<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipe_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_version_id')->constrained('rec_recipe_versions')->onDelete('cascade');
            $table->integer('line_no');

            $table->string('component_kind', 20);
            $table->unsignedBigInteger('component_item_id')->nullable();
            $table->unsignedBigInteger('component_recipe_id')->nullable();

            $table->string('stage_code', 20)->default('main');

            $table->decimal('qty_standard', 20, 6);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

            $table->decimal('wastage_pct_standard', 10, 4)->default(0);
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_modifier_driven')->default(false);

            $table->string('substitution_group_code', 30)->nullable();
            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['recipe_version_id', 'line_no'], 'uq_rec_recipe_components_line');
            $table->index('recipe_version_id', 'idx_rec_recipe_components_recipe_ver');
            $table->index('component_kind', 'idx_rec_recipe_components_kind');
            $table->index('component_item_id', 'idx_rec_recipe_components_item');
            $table->index('component_recipe_id', 'idx_rec_recipe_components_recipe');
            $table->index('stage_code', 'idx_rec_recipe_components_stage');
            $table->index('uom_id', 'idx_rec_recipe_components_uom');

            $table->foreign('component_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('component_recipe_id')->references('id')->on('rec_recipes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipe_components');
    }
};
