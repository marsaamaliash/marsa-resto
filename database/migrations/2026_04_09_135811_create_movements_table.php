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
        Schema::connection('sccr_resto')->create('movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->nullable();

            // Location Tracking
            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');

            // People & Type
            $table->string('pic_name')->nullable();
            $table->string('approved_by_name')->nullable();

            // Movement Type: 'internal_transfer'
            $table->string('type')->default('internal_transfer');

            // Status: 'requested', 'approved_by_exc_Chef', 'approved_by_RM', 'approved_by_supervisor', 'in_transit', 'deliver', 'reject'
            $table->string('status')->default('requested');

            $table->text('remark')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
