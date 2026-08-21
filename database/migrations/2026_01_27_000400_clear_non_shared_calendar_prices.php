<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sharedTourIds = DB::table('tour_shared_users')
            ->select('tour_id')
            ->distinct();

        DB::table('tours')
            ->whereNotIn('id', $sharedTourIds)
            ->update([
                'date_prices' => null,
                'available_dates' => null,
            ]);
    }

    public function down(): void
    {
        // Bu işlem geri alınamaz.
    }
};
























