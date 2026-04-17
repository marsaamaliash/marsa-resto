<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        // Make rec_recipes fields nullable for simplified usage
        Schema::connection('sccr_resto')->table('rec_recipes', function (Blueprint $table) {
            // Fields that may not be used in simplified recipe
            $table->string('recipe_type', 30)->nullable()->change();
            $table->unsignedBigInteger('output_item_id')->nullable()->change();
            $table->unsignedBigInteger('default_uom_id')->nullable()->change();
            $table->string('issue_method', 20)->nullable()->change();
            $table->string('yield_tracking_mode', 20)->nullable()->change();

            // Add menu_id for linking recipe to menu
            $table->unsignedBigInteger('menu_id')->nullable()->after('outlet_id');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('set null');
        });

        // Make rec_recipe_versions fields nullable
        Schema::connection('sccr_resto')->table('rec_recipe_versions', function (Blueprint $table) {
            // Approval workflow fields - making nullable since we don't use approval
            $table->string('approval_status', 20)->nullable()->change();
            $table->decimal('batch_size_qty', 20, 6)->nullable()->change();
            $table->unsignedBigInteger('batch_size_uom_id')->nullable()->change();
            $table->decimal('expected_output_qty', 20, 6)->nullable()->change();
            $table->unsignedBigInteger('expected_output_uom_id')->nullable()->change();
            $table->decimal('expected_yield_pct', 10, 4)->nullable()->change();
            $table->decimal('standard_loss_pct', 10, 4)->nullable()->change();
        });

        // Make rec_recipe_components fields nullable
        Schema::connection('sccr_resto')->table('rec_recipe_components', function (Blueprint $table) {
            // Simplified - we only use item components, not recipe components
            $table->string('component_kind', 20)->nullable()->change();
            $table->unsignedBigInteger('component_recipe_id')->nullable()->change();
            $table->string('stage_code', 20)->nullable()->change();
            $table->decimal('wastage_pct_standard', 10, 4)->nullable()->change();
            $table->boolean('is_optional')->nullable()->change();
            $table->boolean('is_modifier_driven')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Restore original constraints - this is best effort
        Schema::connection('sccr_resto')->table('rec_recipes', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->dropColumn('menu_id');
        });
    }
};
