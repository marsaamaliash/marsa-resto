<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN type ENUM('in', 'out', 'transfer', 'consume', 'adjustment', 'waste', 'reservation', 'unreserved', 'repack_out', 'repack_in')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN type ENUM('in', 'out', 'transfer', 'consume', 'adjustment', 'waste', 'reservation', 'unreserved')");
    }
};
