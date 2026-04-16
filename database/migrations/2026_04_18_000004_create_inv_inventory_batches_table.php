<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('inv_inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('holding_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('outlet_id')->nullable();

            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
            $table->string('batch_no', 50)->nullable();

            $table->date('expiry_date')->nullable();

            $table->decimal('qty_received', 20, 6)->default(0);
            $table->decimal('qty_remaining', 20, 6)->default(0);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

            $table->decimal('unit_cost', 20, 4)->default(0);
            $table->decimal('total_cost', 20, 4)->default(0);
            $table->string('costing_method', 20)->default('batch_actual');

            $table->timestamp('received_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['item_id', 'location_id', 'batch_no'], 'uq_inv_batches_item_loc_batch');
            $table->index(['holding_id', 'branch_id', 'outlet_id'], 'idx_inv_batches_scope');
            $table->index('expiry_date', 'idx_inv_batches_expiry');
            $table->index('is_active', 'idx_inv_batches_active');
            $table->index('costing_method', 'idx_inv_batches_costing');
            $table->index('received_at', 'idx_inv_batches_received');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('inv_inventory_batches');
    }
};
