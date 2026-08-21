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
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'customer_nationality')) {
                $table->string('customer_nationality', 2)->nullable()->after('customer_email')->comment('Customer nationality code (ISO 3166-1 alpha-2)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'customer_nationality')) {
                $table->dropColumn('customer_nationality');
            }
        });
    }
};