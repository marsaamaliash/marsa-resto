<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('rec_recipe_versions', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('rec_recipe_versions', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
