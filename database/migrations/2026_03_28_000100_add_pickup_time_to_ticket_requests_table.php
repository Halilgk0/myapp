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
        Schema::table('ticket_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_requests', 'pickup_time')) {
                $table->time('pickup_time')->nullable()->after('tour_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_requests', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_requests', 'pickup_time')) {
                $table->dropColumn('pickup_time');
            }
        });
    }
};
