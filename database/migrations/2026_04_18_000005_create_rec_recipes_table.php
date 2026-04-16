<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('holding_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();

            $table->string('recipe_code', 50)->unique('uq_rec_recipes_code');
            $table->string('recipe_name');
            $table->string('recipe_type', 30);

            $table->foreignId('output_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('default_uom_id')->constrained('uoms')->onDelete('restrict');

            $table->string('issue_method', 20)->default('batch_actual');
            $table->string('yield_tracking_mode', 20)->default('strict');

            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['holding_id', 'branch_id', 'outlet_id'], 'idx_rec_recipes_scope');
            $table->index('recipe_type', 'idx_rec_recipes_type');
            $table->index('output_item_id', 'idx_rec_recipes_output_item');
            $table->index('default_uom_id', 'idx_rec_recipes_default_uom');
            $table->index('is_active', 'idx_rec_recipes_active');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipes');
    }
};
