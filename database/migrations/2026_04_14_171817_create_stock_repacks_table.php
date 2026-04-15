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
        Schema::connection('sccr_resto')->create('stock_repacks', function (Blueprint $table) {
            $table->id();
            $table->string('repack_number')->unique(); // Contoh: RPC-20260414-0001
            $table->foreignId('location_id')->constrained('locations');

            // Referensi Item
            $table->foreignId('source_item_id')->constrained('items'); // Kecap Kardus
            $table->foreignId('target_item_id')->constrained('items'); // Kecap Botol

            // Angka Konversi
            $table->decimal('qty_source_taken', 15, 2); // Misal: 1 (Kardus)
            $table->decimal('multiplier', 15, 2);       // Misal: 24 (Isi botol per kardus)
            $table->decimal('qty_target_result', 15, 2); // Misal: 24 (Botol)

            $table->foreignId('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_repacks');
    }
};
