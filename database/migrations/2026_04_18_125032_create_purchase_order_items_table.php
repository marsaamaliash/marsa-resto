<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->decimal('ordered_qty', 10, 2);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // PERBAIKAN: Hapus prefix database dari method on()
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onDelete('cascade');
                
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('uom_id')->references('id')->on('uoms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('purchase_order_items');
    }
};