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
            $table->boolean('street_agency_auto_approve_enabled')
                ->default(false)
                ->after('auto_approve_tickets')
                ->comment('Sokak acentası saat bazlı otomatik onay açık/kapalı');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn('street_agency_auto_approve_enabled');
        });
    }
};
