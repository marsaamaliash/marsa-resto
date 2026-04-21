<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->table('movements', function (Blueprint $table) {
            $table->string('request_number')->nullable()->after('reference_number');
            $table->date('request_date')->nullable()->after('request_number');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('movements', function (Blueprint $table) {
            $table->dropColumn(['request_number', 'request_date']);
        });
    }
};
