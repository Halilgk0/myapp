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
        Schema::table('tour_shared_users', function (Blueprint $table) {
            $table->json('custom_date_prices')->nullable()->after('shared_with_user_id');
            $table->json('custom_monthly_prices')->nullable()->after('custom_date_prices');
            $table->json('custom_base_prices')->nullable()->after('custom_monthly_prices');
            $table->string('custom_currency', 5)->nullable()->after('custom_base_prices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_shared_users', function (Blueprint $table) {
            $table->dropColumn([
                'custom_date_prices',
                'custom_monthly_prices',
                'custom_base_prices',
                'custom_currency',
            ]);
        });
    }
};
