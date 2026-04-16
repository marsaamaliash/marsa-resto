<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipe_modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('rec_recipes')->onDelete('cascade');

            $table->string('group_code', 30);
            $table->string('group_name');

            $table->string('selection_mode', 20)->default('single');
            $table->boolean('is_required')->default(false);
            $table->integer('min_select')->default(0);
            $table->integer('max_select')->default(1);
            $table->integer('sort_no')->default(1);

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['recipe_id', 'group_code'], 'uq_rec_modifier_groups_code');
            $table->index('recipe_id', 'idx_rec_modifier_groups_recipe');
            $table->index('is_active', 'idx_rec_modifier_groups_active');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipe_modifier_groups');
    }
};
