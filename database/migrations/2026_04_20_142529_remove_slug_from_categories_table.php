<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('sccr_resto')->hasColumn('categories', 'slug')) {
            Schema::connection('sccr_resto')->table('categories', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('categories', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
        });
    }
};
