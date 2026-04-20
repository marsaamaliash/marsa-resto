<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('uoms', function (Blueprint $table) {
            $table->enum('type', ['weight', 'volume', 'unit'])->nullable()->after('symbols');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('uoms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
