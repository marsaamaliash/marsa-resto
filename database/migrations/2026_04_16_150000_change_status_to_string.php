<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('sccr_resto')->statement('ALTER TABLE orders MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT \'waiting\'');
        DB::connection('sccr_resto')->statement('ALTER TABLE order_items MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT \'waiting\'');
    }

    public function down(): void
    {
        DB::connection('sccr_resto')->statement("ALTER TABLE orders MODIFY COLUMN status ENUM('waiting','ready','deliver','reject','cancelled') DEFAULT 'waiting'");
        DB::connection('sccr_resto')->statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('waiting','ready','deliver','reject') DEFAULT 'waiting'");
    }
};
