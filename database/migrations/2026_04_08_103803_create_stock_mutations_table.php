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

    // Relasi Utama
    $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
    $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
    $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

    /**
     * JENIS MUTASI (Hanya yang mengubah saldo fisik/available)
     * - in: Pembelian/Barang Masuk Luar
     * - out: Barang Keluar (Rusak/Expired langsung dari gudang)
     * - transfer: Perpindahan antar lokasi (Gudang -> Dapur)
     * - consume: Pemakaian bahan baku untuk pesanan (Final)
     * - adjustment: Penyesuaian stok opname
     */
    $table->enum('type', ['in', 'out', 'transfer', 'consume', 'adjustment', 'waste']);

    // Qty yang bermutasi
    $table->decimal('qty', 15, 2);

    /** * AUDIT SALDO (Sangat Penting)
     * Pastikan ini adalah saldo AKHIR TOTAL di lokasi tersebut setelah mutasi.
     */
    $table->decimal('qty_before', 15, 2);
    $table->decimal('qty_after', 15, 2);

    // Referensi Polimorfik (Agar bisa nyambung ke model StockRequest, Order, atau PurchaseOrder)
    // $table->nullableMorphs('reference'??); 

    /**
     * DETAIL TRANSFER
     * Jika type = 'transfer', simpan asal dan tujuannya.
     */
    $table->foreignId('from_location_id')->nullable()->constrained('locations');
    $table->foreignId('to_location_id')->nullable()->constrained('locations');

    $table->string('user_id');
    $table->text('notes')->nullable();
    $table->timestamps();

    // Index untuk performa laporan
    $table->index(['item_id', 'location_id', 'type']);
    // $table->index(['reference_type', 'reference_id']);
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
