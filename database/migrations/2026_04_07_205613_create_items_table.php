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
        Schema::connection('sccr_resto')->create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->comment('Stock Keeping Unit / Barcode');
            $table->text('description')->nullable();

            // Relasi ke Master Data lain
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('uom_id')->constrained('uoms')->onDelete('restrict');

            // Requirement: Stok Kritis (Tugas tgl 12-13 Apr)
            // Menggunakan decimal agar bisa mendukung satuan Kg (misal: 0.5 Kg)
            $table->decimal('min_stock', 15, 2)->default(0);

            // Pengaturan Item
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stockable')->default(true)->comment('Apakah barang ini dihitung stoknya?');

            // Flag untuk Batch & Expiry (Persiapan tugas tgl 23 Apr)
            $table->boolean('has_batch')->default(false);
            $table->boolean('has_expiry')->default(false);

            $table->enum('type', ['raw', 'prep', 'menu'])->default('raw');

            $table->timestamps();
            $table->softDeletes(); // Jaga-jaga kalau salah hapus, data tidak hilang permanen
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
