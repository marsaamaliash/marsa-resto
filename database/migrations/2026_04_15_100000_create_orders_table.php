<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('table_number')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::connection('sccr_resto')->create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'ready', 'served'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('order_items');
        Schema::connection('sccr_resto')->dropIfExists('orders');
    }
};
