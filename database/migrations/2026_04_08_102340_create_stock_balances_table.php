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
     Schema::connection('sccr_resto')->create('stock_balances', function (Blueprint $table) {
            $table->id();
               $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
               $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
               $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');
               $table->decimal('qty_available', 15, 2)->default(0);
               $table->decimal('qty_reserved', 15, 2)->default(0);
               $table->decimal('qty_waste', 15, 2)->default(0);

               $table->unique(['item_id', 'location_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
