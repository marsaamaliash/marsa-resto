<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::connection('sccr_resto')->table('purchase_requests', function (Blueprint $table) {
            $table->string('created_by', 100)->nullable()->after('revise_requested_at_level');
            $table->string('updated_by', 100)->nullable()->after('created_by');
        });

        Schema::connection('sccr_resto')->table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::connection('sccr_resto')->table('purchase_orders', function (Blueprint $table) {
            $table->string('created_by', 100)->nullable()->after('revise_requested_at_level');
            $table->string('updated_by', 100)->nullable()->after('created_by');
        });

        Schema::connection('sccr_resto')->table('direct_orders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::connection('sccr_resto')->table('direct_orders', function (Blueprint $table) {
            $table->string('created_by', 100)->nullable()->after('revise_requested_at_level');
            $table->string('updated_by', 100)->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::connection('sccr_resto')->table('purchase_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('revise_requested_at_level');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });

        Schema::connection('sccr_resto')->table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::connection('sccr_resto')->table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('revise_requested_at_level');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });

        Schema::connection('sccr_resto')->table('direct_orders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::connection('sccr_resto')->table('direct_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('revise_requested_at_level');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });
    }
};
