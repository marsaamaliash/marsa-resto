<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');

            $table->decimal('requested_qty', 20, 6);
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');
            $table->decimal('unit_cost', 20, 2)->nullable();
            $table->decimal('total_cost', 20, 2)->nullable();

            $table->boolean('is_critical')->default(false);
            $table->decimal('actual_stock', 20, 6)->default(0);
            $table->decimal('min_stock', 20, 6)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('purchase_request_id', 'idx_pri_pr_id');
            $table->index('item_id', 'idx_pri_item_id');
            $table->index('is_critical', 'idx_pri_is_critical');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('purchase_request_items');
    }
};
