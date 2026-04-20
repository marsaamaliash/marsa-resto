<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('direct_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('direct_order_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('direct_order_id')->references('id')->on('direct_orders')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('uom_id')->references('id')->on('uoms');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('direct_order_items');
    }
};
