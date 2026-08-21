<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tours') && !Schema::hasColumn('tours', 'district')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->string('district', 100)->nullable()->after('region');
                $table->index('district');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tours') && Schema::hasColumn('tours', 'district')) {
            Schema::table('tours', function (Blueprint $table) {
                $table->dropIndex(['district']);
                $table->dropColumn('district');
            });
        }
    }
};







