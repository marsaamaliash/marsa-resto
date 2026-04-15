<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sccr_resto')->create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('category')->nullable();
            $table->string('customer_segment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('slug')->unique()->nullable();
            $table->timestamps();
            // $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('set null'); tunggu table recipes dulu
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->dropIfExists('menus');
    }
};
