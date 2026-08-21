<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('rest_adjustment_amount', 12, 2)->nullable()->after('owner_share_currency');
            $table->string('rest_adjustment_currency', 5)->nullable()->after('rest_adjustment_amount');
        });

        Schema::table('ticket_requests', function (Blueprint $table) {
            $table->decimal('rest_adjustment_amount', 12, 2)->nullable()->after('total_price');
            $table->string('rest_adjustment_currency', 5)->nullable()->after('rest_adjustment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['rest_adjustment_amount', 'rest_adjustment_currency']);
        });

        Schema::table('ticket_requests', function (Blueprint $table) {
            $table->dropColumn(['rest_adjustment_amount', 'rest_adjustment_currency']);
        });
    }
};






























