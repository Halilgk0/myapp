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
            $table->integer('adult_count')->default(0)->after('created_by_user_id');
            $table->integer('child_count')->default(0)->after('adult_count');
            $table->integer('infant_count')->default(0)->after('child_count');
            $table->decimal('adult_price', 10, 2)->default(0)->after('infant_count');
            $table->decimal('child_price', 10, 2)->default(0)->after('adult_price');
            $table->decimal('infant_price', 10, 2)->default(0)->after('child_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['adult_count', 'child_count', 'infant_count', 'adult_price', 'child_price', 'infant_price']);
        });
    }
};
