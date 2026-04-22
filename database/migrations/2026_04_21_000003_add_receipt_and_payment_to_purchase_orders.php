<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->table('purchase_orders', function (Blueprint $table) {
            $table->enum('received_status', ['not_received', 'partial', 'fully_received'])->default('not_received')->after('status');
            $table->boolean('is_closed')->default(false)->after('received_status');
            $table->enum('payment_status', ['unpaid', 'pending_finance', 'paid'])->default('unpaid')->after('is_closed');
            $table->string('invoice_number')->nullable()->after('payment_status');
            $table->date('invoice_date')->nullable()->after('invoice_number');
            $table->string('invoice_path')->nullable()->after('invoice_date');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['received_status', 'is_closed', 'payment_status', 'invoice_number', 'invoice_date', 'invoice_path']);
        });
    }
};
