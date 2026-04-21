<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('direct_order_items');
        Schema::connection('sccr_resto')->dropIfExists('direct_orders');

        Schema::connection('sccr_resto')->create('direct_orders', function (Blueprint $table) {
            $table->id();
            $table->string('do_number')->unique();
            $table->unsignedBigInteger('location_id');
            $table->string('purchaser_name');
            $table->date('purchase_date');
            $table->string('payment_by')->default('holding');
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('status')->default('draft');
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

            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('direct_orders');
    }
};
