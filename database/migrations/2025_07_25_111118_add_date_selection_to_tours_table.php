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
        Schema::table('tours', function (Blueprint $table) {
            // Tarih seçimi için alanlar - sadece yoksa ekle
            if (!Schema::hasColumn('tours', 'available_days')) {
                $table->json('available_days')->nullable()->after('image'); // [1,2,3,4,5,6,7] (Pazartesi=1, Pazar=7)
            }
            if (!Schema::hasColumn('tours', 'available_months')) {
                $table->json('available_months')->nullable()->after('available_days'); // [1,2,3,4,5,6,7,8,9,10,11,12]
            }
            if (!Schema::hasColumn('tours', 'available_years')) {
                $table->json('available_years')->nullable()->after('available_months'); // [2024,2025,2026]
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['available_days', 'available_months', 'available_years']);
        });
    }
};
