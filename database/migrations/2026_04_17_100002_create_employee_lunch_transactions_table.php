<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('employee_lunch_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number');
            $table->json('items');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('allowance_used', 12, 2)->default(0);
            $table->decimal('excess_amount', 12, 2)->default(0);
            $table->string('payment_method')->default('allowance');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('employee_lunch_transactions');
    }
};
