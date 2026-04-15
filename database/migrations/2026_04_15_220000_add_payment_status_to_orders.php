<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->after('status');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_at']);
        });
    }
};
