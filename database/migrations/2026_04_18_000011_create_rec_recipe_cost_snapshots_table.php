<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('rec_recipe_cost_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_version_id')->constrained('rec_recipe_versions')->onDelete('cascade');

            $table->date('snapshot_date');
            $table->string('cost_basis', 20)->default('latest_batch_actual');

            $table->decimal('material_cost', 20, 4)->default(0);
            $table->decimal('packaging_cost', 20, 4)->default(0);
            $table->decimal('overhead_cost', 20, 4)->default(0);
            $table->decimal('labor_cost', 20, 4)->default(0);

            $table->decimal('total_batch_cost', 20, 4)->default(0);
            $table->decimal('cost_per_output_unit', 20, 4)->default(0);

            $table->string('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['recipe_version_id', 'snapshot_date', 'cost_basis'], 'uq_rec_cost_snapshots_ver_date_basis');
            $table->index('recipe_version_id', 'idx_rec_cost_snapshots_recipe_ver');
            $table->index('snapshot_date', 'idx_rec_cost_snapshots_date');
            $table->index('cost_basis', 'idx_rec_cost_snapshots_basis');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('rec_recipe_cost_snapshots');
    }
};
