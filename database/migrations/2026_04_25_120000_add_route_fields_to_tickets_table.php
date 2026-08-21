<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'is_route_start')) {
                $table->boolean('is_route_start')->default(false)->after('driver_id');
            }
            if (!Schema::hasColumn('tickets', 'route_order')) {
                $table->unsignedSmallInteger('route_order')->nullable()->after('is_route_start');
            }
            if (!Schema::hasColumn('tickets', 'route_optimized_at')) {
                $table->timestamp('route_optimized_at')->nullable()->after('route_order');
            }
        });

        // Index for fast driver+date+order lookups
        Schema::table('tickets', function (Blueprint $table) {
            $indexExists = collect(\DB::select("SHOW INDEX FROM tickets WHERE Key_name = 'tickets_driver_route_idx'"))
                ->isNotEmpty();
            if (!$indexExists) {
                $table->index(['driver_id', 'tour_date', 'route_order'], 'tickets_driver_route_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            try {
                $table->dropIndex('tickets_driver_route_idx');
            } catch (\Throwable $e) { /* ignore */ }
            $columns = array_filter([
                Schema::hasColumn('tickets', 'is_route_start') ? 'is_route_start' : null,
                Schema::hasColumn('tickets', 'route_order') ? 'route_order' : null,
                Schema::hasColumn('tickets', 'route_optimized_at') ? 'route_optimized_at' : null,
            ]);
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
