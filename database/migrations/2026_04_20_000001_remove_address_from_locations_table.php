<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        if (Schema::connection('sccr_resto')->hasColumn('locations', 'address')) {
            Schema::connection('sccr_resto')->table('locations', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('locations', function (Blueprint $table) {
            $table->text('address')->nullable()->after('pic_name');
        });
    }
};
