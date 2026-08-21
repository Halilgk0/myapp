<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tours')->update([
            'price_adult' => 0,
            'price_child' => 0,
            'price_infant' => 0,
            'monthly_prices' => null,
        ]);
    }

    public function down(): void
    {
        // Legacy fiyatları geri getiremeyiz.
    }
};
























