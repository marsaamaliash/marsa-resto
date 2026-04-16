<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('menus', function (Blueprint $table) {
            $table->foreign('recipe_id')->references('id')->on('rec_recipes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('menus', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
        });
    }
};
