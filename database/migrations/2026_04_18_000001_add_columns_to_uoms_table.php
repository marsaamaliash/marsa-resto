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
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'abbreviation')) {
                $table->string('abbreviation', 20)->nullable()->after('symbols');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'holding_id')) {
                $table->unsignedBigInteger('holding_id')->nullable()->after('is_active');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('holding_id');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'outlet_id')) {
                $table->unsignedBigInteger('outlet_id')->nullable()->after('branch_id');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'notes')) {
                $table->text('notes')->nullable()->after('outlet_id');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('deleted_at');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (! Schema::connection('sccr_resto')->hasColumn('uoms', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('uoms', function (Blueprint $table) {
            $table->dropColumn([
                'abbreviation',
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
