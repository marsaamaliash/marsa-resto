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
        Schema::connection('sccr_resto')->create('uom_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items'); // Kadang konversi beda tiap item
            $table->foreignId('from_uom_id')->constrained('uoms'); // misal: Kardus
            $table->foreignId('to_uom_id')->constrained('uoms');   // misal: Botol
            $table->decimal('multiplier', 15, 2); // misal: 24.00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
    }
};
