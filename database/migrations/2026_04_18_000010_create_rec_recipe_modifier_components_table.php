<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipe_modifier_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained('rec_recipe_modifier_groups')->onDelete('cascade');

            $table->string('modifier_code', 30);
            $table->string('modifier_name');

            $table->string('component_kind', 20)->default('item');
            $table->unsignedBigInteger('component_item_id')->nullable();
            $table->unsignedBigInteger('component_recipe_id')->nullable();

            $table->decimal('additional_qty', 20, 6)->default(0);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');
            $table->decimal('additional_price', 20, 4)->default(0);

            $table->integer('sort_no')->default(1);
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['modifier_group_id', 'modifier_code'], 'uq_rec_modifier_components_code');
            $table->index('modifier_group_id', 'idx_rec_modifier_components_group');
            $table->index('component_item_id', 'idx_rec_modifier_components_item');
            $table->index('component_recipe_id', 'idx_rec_modifier_components_recipe');
            $table->index('uom_id', 'idx_rec_modifier_components_uom');
            $table->index('is_active', 'idx_rec_modifier_components_active');

            $table->foreign('component_item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('component_recipe_id')->references('id')->on('rec_recipes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipe_modifier_components');
    }
};
