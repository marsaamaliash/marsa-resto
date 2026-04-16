<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('holding_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();

            $table->string('prod_no', 50)->unique('uq_prod_orders_no');
            $table->string('prod_type', 30);

            $table->foreignId('recipe_id')->constrained('rec_recipes')->onDelete('restrict');
            $table->foreignId('recipe_version_id')->constrained('rec_recipe_versions')->onDelete('restrict');

            $table->string('source_table')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_no', 50)->nullable();

            $table->foreignId('issue_location_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('output_location_id')->constrained('locations')->onDelete('restrict');

            $table->decimal('planned_output_qty', 20, 6)->default(0);
            $table->decimal('actual_output_qty', 20, 6)->default(0);
            $table->foreignId('output_uom_id')->constrained('uoms')->onDelete('restrict');

            $table->string('status', 20)->default('draft');
            $table->string('approval_status', 20)->default('not_required');
            $table->unsignedBigInteger('approval_request_id')->nullable();

            $table->date('business_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

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

            $table->index(['holding_id', 'branch_id', 'outlet_id'], 'idx_prod_orders_scope');
            $table->index('prod_type', 'idx_prod_orders_type');
            $table->index('status', 'idx_prod_orders_status');
            $table->index('approval_status', 'idx_prod_orders_approval');
            $table->index('recipe_id', 'idx_prod_orders_recipe');
            $table->index('recipe_version_id', 'idx_prod_orders_recipe_ver');
            $table->index(['source_table', 'source_id'], 'idx_prod_orders_source');
            $table->index('issue_location_id', 'idx_prod_orders_issue_loc');
            $table->index('output_location_id', 'idx_prod_orders_output_loc');
            $table->index('output_uom_id', 'idx_prod_orders_output_uom');
            $table->index('business_date', 'idx_prod_orders_business_date');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_orders');
    }
};
