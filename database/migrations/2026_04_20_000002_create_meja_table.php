<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        if (! Schema::connection('sccr_resto')->hasTable('meja')) {
            Schema::connection('sccr_resto')->create('meja', function (Blueprint $table) {
                $table->id();
                $table->string('table_number')->unique();
                $table->integer('capacity')->default(2);
                $table->enum('area', ['indoor', 'outdoor', 'vip', 'smoking', 'non-smoking'])->default('indoor');
                $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('meja');
    }
};
