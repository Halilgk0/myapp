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
            if (!Schema::hasColumn('tours', 'street_agency_auto_approve_time')) {
                $table->time('street_agency_auto_approve_time')
                    ->nullable()
                    ->after('auto_approve_tickets')
                    ->comment('Sokak acentası otomatik onay başlangıç saati');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'street_agency_auto_approve_time')) {
                $table->dropColumn('street_agency_auto_approve_time');
            }
        });
    }
};
