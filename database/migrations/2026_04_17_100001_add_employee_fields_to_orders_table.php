<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('orders', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->after('customer_name');
            $table->string('order_type')->default('regular')->after('employee_number');
            $table->decimal('allowance_used', 12, 2)->default(0)->after('order_type');
            $table->decimal('excess_amount', 12, 2)->default(0)->after('allowance_used');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('orders', function (Blueprint $table) {
            $table->dropColumn(['employee_number', 'order_type', 'allowance_used', 'excess_amount']);
        });
    }
};
