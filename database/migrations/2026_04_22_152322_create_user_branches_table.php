<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('user_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_user_id');
            $table->unsignedBigInteger('branch_id');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();
            $table->timestamps();

            $table->unique(['auth_user_id', 'branch_id']);
            $table->index('auth_user_id');
            $table->index('branch_id');
            $table->index('is_default');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('user_branches');
    }
};
