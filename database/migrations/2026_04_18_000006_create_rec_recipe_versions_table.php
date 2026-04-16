<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipe_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('rec_recipes')->onDelete('cascade');

            $table->integer('version_no');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->string('approval_status', 20)->default('draft');

            $table->unsignedBigInteger('approval_request_id')->nullable();

            $table->decimal('batch_size_qty', 20, 6)->default(1);
            $table->foreignId('batch_size_uom_id')->constrained('uoms')->onDelete('restrict');

            $table->decimal('expected_output_qty', 20, 6)->default(1);
            $table->foreignId('expected_output_uom_id')->constrained('uoms')->onDelete('restrict');

            $table->decimal('expected_yield_pct', 10, 4)->default(100);
            $table->decimal('standard_loss_pct', 10, 4)->default(0);

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('reject_reason')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['recipe_id', 'version_no'], 'uq_rec_recipe_versions_recipe_ver');
            $table->index('recipe_id', 'idx_rec_recipe_versions_recipe');
            $table->index(['effective_from', 'effective_to'], 'idx_rec_recipe_versions_effective');
            $table->index('approval_status', 'idx_rec_recipe_versions_approval');
            $table->index('batch_size_uom_id', 'idx_rec_recipe_versions_batch_uom');
            $table->index('expected_output_uom_id', 'idx_rec_recipe_versions_output_uom');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipe_versions');
    }
};
