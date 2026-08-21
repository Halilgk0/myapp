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
        Schema::table('ticket_passengers', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_passengers', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_passengers', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_passengers', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
        });
    }
};
