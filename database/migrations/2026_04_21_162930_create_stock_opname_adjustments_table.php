<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('stock_opname_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('location_id')->constrained('locations');
            $table->foreignId('uom_id')->nullable()->constrained('uoms');
            $table->decimal('system_qty', 15, 2)->default(0);
            $table->decimal('physical_qty', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            $table->string('status')->default('match');
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->index(['stock_opname_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('stock_opname_adjustments');
    }
};
