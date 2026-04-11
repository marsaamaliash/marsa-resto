<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->table('movements', function (Blueprint $table) {
            $table->tinyInteger('approval_level')->nullable()->after('status');
            $table->string('exc_chef_approved_by')->nullable()->after('approval_level');
            $table->timestamp('exc_chef_approved_at')->nullable()->after('exc_chef_approved_by');
            $table->string('rm_approved_by')->nullable()->after('exc_chef_approved_at');
            $table->timestamp('rm_approved_at')->nullable()->after('rm_approved_by');
            $table->string('spv_approved_by')->nullable()->after('rm_approved_at');
            $table->timestamp('spv_approved_at')->nullable()->after('spv_approved_by');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('movements', function (Blueprint $table) {
            $table->dropColumn([
                'approval_level',
                'exc_chef_approved_by',
                'exc_chef_approved_at',
                'rm_approved_by',
                'rm_approved_at',
                'spv_approved_by',
                'spv_approved_at',
            ]);
        });
    }
};
