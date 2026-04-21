<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('location_id')->constrained('locations');
            $table->string('checker_name')->nullable();
            $table->string('checker_role')->nullable();
            $table->string('witness_name')->nullable();
            $table->string('witness_role')->nullable();
            $table->date('opname_date');
            $table->string('status')->default('draft');
            $table->integer('approval_level')->default(0);
            $table->string('exc_chef_approved_by')->nullable();
            $table->timestamp('exc_chef_approved_at')->nullable();
            $table->string('rm_approved_by')->nullable();
            $table->timestamp('rm_approved_at')->nullable();
            $table->string('spv_approved_by')->nullable();
            $table->timestamp('spv_approved_at')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'location_id', 'opname_date']);
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('stock_opnames');
    }
};
