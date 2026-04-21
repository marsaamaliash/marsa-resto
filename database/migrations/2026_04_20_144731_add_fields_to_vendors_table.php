<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_resto';

    public function up(): void
    {
        Schema::connection('sccr_resto')->table('vendors', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('pic')->nullable()->after('email');
            $table->text('description')->nullable()->after('address');
            $table->enum('default_terms', ['cash', '7_hari', '30_hari'])->nullable()->after('description');
            $table->enum('status', ['requested', 'approved', 'rejected'])->default('requested')->after('default_terms');
            $table->text('rejection_reason')->nullable()->after('status');
        });

        Schema::connection('sccr_resto')->table('vendors', function (Blueprint $table) {
            $table->string('no_telp')->nullable()->change();
            $table->string('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('sccr_resto')->table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'pic',
                'description',
                'default_terms',
                'status',
                'rejection_reason',
            ]);

            $table->string('no_telp')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
        });
    }
};
