<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->enum('status', ['draft', 'pending_rm', 'pending_spv', 'approved', 'rejected'])->default('draft');
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
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('goods_receipts');
    }
};
