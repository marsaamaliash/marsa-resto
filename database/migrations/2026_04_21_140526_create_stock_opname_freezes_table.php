<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('stock_opname_freezes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations');
            $table->string('reference_number');
            $table->string('frozen_by')->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->string('status')->default('frozen');
            $table->timestamps();

            $table->index(['location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('stock_opname_freezes');
    }
};
