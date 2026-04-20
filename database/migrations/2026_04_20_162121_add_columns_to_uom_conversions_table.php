<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->table('uom_conversions', function (Blueprint $table) {
            $table->renameColumn('from_uom_id', 'from_uoms_id');
            $table->renameColumn('to_uom_id', 'to_uoms_id');
            $table->renameColumn('multiplier', 'conversion_factor');

            $table->text('notes')->nullable()->after('conversion_factor');
            $table->foreignId('created_by')->nullable()->after('notes');
            $table->foreignId('updated_by')->nullable()->after('created_by');
            $table->foreignId('deleted_by')->nullable()->after('updated_by');
            $table->softDeletes()->after('deleted_by');

            $table->unique(['item_id', 'from_uoms_id', 'to_uoms_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('uom_conversions', function (Blueprint $table) {
            $table->dropUnique(['item_id', 'from_uoms_id', 'to_uoms_id']);
            $table->dropSoftDeletes();
            $table->dropColumn(['deleted_by', 'updated_by', 'created_by', 'notes']);

            $table->renameColumn('from_uoms_id', 'from_uom_id');
            $table->renameColumn('to_uoms_id', 'to_uom_id');
            $table->renameColumn('conversion_factor', 'multiplier');
        });
    }
};
