<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('base_currency', 5)->nullable()->after('currency');
            $table->string('sale_currency', 5)->nullable()->after('base_currency');
            $table->decimal('rest_converted_amount', 12, 2)->nullable()->after('rest_adjustment_currency');
            $table->decimal('rest_fx_rate', 18, 8)->nullable()->after('rest_converted_amount'); // target per source
            $table->string('rest_fx_source_currency', 5)->nullable()->after('rest_fx_rate');
            $table->string('rest_fx_target_currency', 5)->nullable()->after('rest_fx_source_currency');
            $table->date('rest_fx_date')->nullable()->after('rest_fx_target_currency');
        });

        Schema::table('ticket_requests', function (Blueprint $table) {
            $table->string('base_currency', 5)->nullable()->after('currency');
            $table->string('sale_currency', 5)->nullable()->after('base_currency');
            $table->decimal('rest_converted_amount', 12, 2)->nullable()->after('rest_adjustment_currency');
            $table->decimal('rest_fx_rate', 18, 8)->nullable()->after('rest_converted_amount');
            $table->string('rest_fx_source_currency', 5)->nullable()->after('rest_fx_rate');
            $table->string('rest_fx_target_currency', 5)->nullable()->after('rest_fx_source_currency');
            $table->date('rest_fx_date')->nullable()->after('rest_fx_target_currency');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'base_currency',
                'sale_currency',
                'rest_converted_amount',
                'rest_fx_rate',
                'rest_fx_source_currency',
                'rest_fx_target_currency',
                'rest_fx_date',
            ]);
        });

        Schema::table('ticket_requests', function (Blueprint $table) {
            $table->dropColumn([
                'base_currency',
                'sale_currency',
                'rest_converted_amount',
                'rest_fx_rate',
                'rest_fx_source_currency',
                'rest_fx_target_currency',
                'rest_fx_date',
            ]);
        });
    }
};





























