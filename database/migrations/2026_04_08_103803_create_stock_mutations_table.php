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
        Schema::connection('sccr_resto')->create('stock_mutations', function (Blueprint $table) {
    $table->id();

    // Relasi utama
    $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
    $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
    $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

    // Jenis mutasi
    $table->enum('type', [
        'in', 'out', 'transfer_in', 'transfer_out', 'adjustment',
    'reserve',   // Mengunci stok untuk order
    'unreserve', // Membatalkan kunci (balikin ke available)
    'consume',   // Selesai masak dari reserved
    'waste'
    ]);

    // Qty
    $table->decimal('qty', 15, 2);

    // Optional tapi penting untuk audit
    $table->decimal('qty_before', 15, 2)->nullable();
    $table->decimal('qty_after', 15, 2)->nullable();

    // Referensi dokumen (fleksibel)
    $table->string('reference_type')->nullable(); // PO, GR, RETUR, dll
    $table->unsignedBigInteger('reference_id')->nullable();

    // Untuk transfer (biar tidak absurd)
    $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
    $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();

    // Catatan
    $table->text('notes')->nullable();

    $table->timestamps();

    // Index biar tidak lemot nanti
    $table->index(['item_id', 'location_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
