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
        Schema::connection('sccr_resto')->create('request_activities', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel utama movements
            $table->foreignId('movement_id')->nullable()->constrained('movements')->onDelete('cascade');
            $table->string('pic'); // Siapa yang melakukan aksi (RM, Chef, dll)

            $table->string('action'); // Contoh: 'requested', 'revised', 'approved_rm', 'distributed'
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();

            // Menyimpan catatan jika ada penolakan atau alasan revisi
            $table->text('comment')->nullable();

            // Sangat berguna jika Exc. Chef mengubah qty, simpan perubahannya di sini
            $table->json('changes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_activities');
    }
};
