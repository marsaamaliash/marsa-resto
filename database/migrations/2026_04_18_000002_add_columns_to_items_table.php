<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('holding_id')->nullable()->after('deleted_at');
            $table->unsignedBigInteger('branch_id')->nullable()->after('holding_id');
            $table->unsignedBigInteger('outlet_id')->nullable()->after('branch_id');
            $table->decimal('cost_standard', 20, 4)->nullable()->after('outlet_id');
            $table->string('cost_method', 20)->default('moving_average')->after('cost_standard');
            $table->unsignedBigInteger('created_by')->nullable()->after('cost_method');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('items', function (Blueprint $table) {
            $table->dropColumn([
                'holding_id',
                'branch_id',
                'outlet_id',
                'cost_standard',
                'cost_method',
                'created_by',
                'updated_by',
                'deleted_by',
            ]);
        });
    }
};
