<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add city column if it does not exist
        if (!Schema::hasColumn('tours', 'city')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->string('city', 100)->nullable()->after('country');
            });
        }

        // Backfill city from region if region exists
        if (Schema::hasColumn('tours', 'region') && Schema::hasColumn('tours', 'city')) {
            // Copy values from region to city where city is null or empty
            DB::statement("UPDATE tours SET city = COALESCE(NULLIF(city, ''), region) WHERE city IS NULL OR city = ''");
        }

        // Drop region column if exists
        if (Schema::hasColumn('tours', 'region')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropColumn('region');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add region if missing
        if (!Schema::hasColumn('tours', 'region')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->string('region', 100)->nullable()->after('country');
            });
        }

        // Backfill region from city
        if (Schema::hasColumn('tours', 'region') && Schema::hasColumn('tours', 'city')) {
            DB::statement("UPDATE tours SET region = COALESCE(NULLIF(region, ''), city) WHERE region IS NULL OR region = ''");
        }

        // Drop city column if exists
        if (Schema::hasColumn('tours', 'city')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropColumn('city');
            });
        }
    }
};







