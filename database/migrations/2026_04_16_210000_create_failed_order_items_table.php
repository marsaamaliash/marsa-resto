<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('failed_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_order_item_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('menu_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('notes')->nullable();
            $table->string('reject_reason', 500);
            $table->string('status', 50)->default('failed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('failed_order_items');
    }
};
