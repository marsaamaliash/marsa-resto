<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number', 50)->unique();
            $table->foreignId('requester_location_id')->constrained('locations')->onDelete('restrict');
            $table->string('vendor_name', 100)->nullable();

            $table->string('status', 20)->default('draft');
            $table->integer('approval_level')->default(0);

            $table->text('notes')->nullable();
            $table->date('required_date')->nullable();
            $table->decimal('total_estimated_cost', 20, 2)->default(0);

            $table->string('requested_by', 100)->nullable();
            $table->timestamp('requested_at')->nullable();

            $table->string('rm_approved_by', 100)->nullable();
            $table->timestamp('rm_approved_at')->nullable();
            $table->text('rm_notes')->nullable();

            $table->string('spv_approved_by', 100)->nullable();
            $table->timestamp('spv_approved_at')->nullable();
            $table->text('spv_notes')->nullable();

            $table->string('rejected_by', 100)->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->integer('rejected_at_level')->nullable();

            $table->string('revise_requested_by', 100)->nullable();
            $table->timestamp('revise_requested_at')->nullable();
            $table->text('revise_reason')->nullable();
            $table->integer('revise_requested_at_level')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_pr_status');
            $table->index('approval_level', 'idx_pr_approval_level');
            $table->index('requester_location_id', 'idx_pr_location');
            $table->index('requested_at', 'idx_pr_requested_at');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('purchase_requests');
    }
};
