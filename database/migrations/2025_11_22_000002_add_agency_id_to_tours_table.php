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
            if (!Schema::hasColumn('tours', 'agency_id')) {
                $table->foreignId('agency_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('agencies')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('tours', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('agency_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
            $table->dropColumn('agency_id');
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};

