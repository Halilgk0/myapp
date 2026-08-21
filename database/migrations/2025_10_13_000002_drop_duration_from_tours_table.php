<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'duration_days')) {
                $table->dropColumn('duration_days');
            }
            if (Schema::hasColumn('tours', 'duration_hours')) {
                $table->dropColumn('duration_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (!Schema::hasColumn('tours', 'duration_days')) {
                $table->integer('duration_days')->default(0);
            }
            if (!Schema::hasColumn('tours', 'duration_hours')) {
                $table->integer('duration_hours')->default(0);
            }
        });
    }
};




