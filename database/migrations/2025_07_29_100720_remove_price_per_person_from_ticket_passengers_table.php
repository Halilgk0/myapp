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
            // price_per_person alanını kaldır (eğer varsa)
            if (Schema::hasColumn('ticket_passengers', 'price_per_person')) {
                $table->dropColumn('price_per_person');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_passengers', function (Blueprint $table) {
            // Geri almak için price_per_person alanını tekrar ekle
            if (!Schema::hasColumn('ticket_passengers', 'price_per_person')) {
                $table->decimal('price_per_person', 10, 2)->nullable()->after('quantity');
            }
        });
    }
};
