<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('holding_id')->nullable()->after('deleted_at');
            $table->unsignedBigInteger('branch_id')->nullable()->after('holding_id');
            $table->unsignedBigInteger('outlet_id')->nullable()->after('branch_id');
            $table->text('notes')->nullable()->after('outlet_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'holding_id',
                'branch_id',
                'outlet_id',
                'notes',
                'created_by',
                'updated_by',
                'deleted_by',
            ]);
        });
    }
};
