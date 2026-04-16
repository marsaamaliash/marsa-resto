<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->create('prod_material_issue_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_line_id')->constrained('prod_material_issue_lines')->onDelete('cascade');

            $table->foreignId('inventory_batch_id')->constrained('inv_inventory_batches')->onDelete('restrict');
            $table->string('batch_no', 50)->nullable();
            $table->date('expiry_date')->nullable();

            $table->decimal('qty_issued_base', 20, 6)->default(0);
            $table->decimal('unit_cost', 20, 4)->default(0);
            $table->decimal('total_cost', 20, 4)->default(0);

            $table->integer('issue_sequence')->default(1);

            $table->timestamps();

            $table->unique(['issue_line_id', 'issue_sequence'], 'uq_prod_issue_batches_seq');
            $table->index('issue_line_id', 'idx_prod_issue_batches_issue_line');
            $table->index('inventory_batch_id', 'idx_prod_issue_batches_batch');
            $table->index('expiry_date', 'idx_prod_issue_batches_expiry');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('prod_material_issue_batches');
    }
};
