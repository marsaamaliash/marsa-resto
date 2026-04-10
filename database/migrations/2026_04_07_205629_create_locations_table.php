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
        Schema::connection('sccr_resto')->create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Gudang Utama", "Kitchen A"
            $table->string('code')->unique(); // Contoh: WH-01, KIT-01

            /**
             * Tipe Lokasi sangat penting untuk Rule Transaksi:
             * - warehouse: Tempat terima barang dari Vendor (PO)
             * - kitchen/outlet: Tempat produksi atau jualan
             * - transit: Tempat penampung sementara saat pindah barang (Transfer)
             */
            $table->enum('type', ['warehouse', 'kitchen', 'outlet', 'transit'])
                ->default('warehouse');

            $table->string('pic_name')->nullable(); // Penanggung jawab lokasi
            $table->text('address')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
