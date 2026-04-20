<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('purchase_order_items');
        Schema::connection('sccr_resto')->dropIfExists('purchase_orders');

        Schema::connection('sccr_resto')->create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('vendor_name');
            $table->unsignedBigInteger('location_id');
            $table->enum('payment_by', ['holding', 'resto'])->default('holding');
            $table->string('quotation_path')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->enum('status', ['draft', 'pending_rm', 'pending_spv', 'approved', 'rejected', 'revised'])->default('draft');
            $table->unsignedTinyInteger('approval_level')->default(0);
            $table->unsignedBigInteger('rm_approved_by')->nullable();
            $table->dateTime('rm_approved_at')->nullable();
            $table->text('rm_notes')->nullable();
            $table->unsignedBigInteger('spv_approved_by')->nullable();
            $table->dateTime('spv_approved_at')->nullable();
            $table->text('spv_notes')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->unsignedTinyInteger('rejected_at_level')->nullable();
            $table->unsignedBigInteger('revise_requested_by')->nullable();
            $table->dateTime('revise_requested_at')->nullable();
            $table->text('revise_reason')->nullable();
            $table->unsignedTinyInteger('revise_requested_at_level')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('purchase_request_id')->references('id')->on('sccr_resto.purchase_requests');
            $table->foreign('vendor_id')->references('id')->on('sccr_resto.vendors');
            $table->foreign('location_id')->references('id')->on('sccr_resto.locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('purchase_orders');
    }
};
