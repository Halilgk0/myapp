<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (!Schema::hasColumn('tours', 'agency_id')) {
                $table->foreignId('agency_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('agencies')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('tours', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->after('agency_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        $defaultAdminId = DB::table('users')
            ->where('level', User::LEVEL_ADMIN ?? 1)
            ->orderBy('id')
            ->value('id');

        DB::table('tours')
            ->orderBy('id')
            ->chunkById(100, function ($tours) use ($defaultAdminId) {
                foreach ($tours as $tour) {
                    if ($tour->owner_id) {
                        continue;
                    }

                    $ownerId = DB::table('agencies')
                        ->where('id', $tour->agency_id)
                        ->value('user_id') ?? $defaultAdminId;

                    if ($ownerId) {
                        DB::table('tours')
                            ->where('id', $tour->id)
                            ->update(['owner_id' => $ownerId]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'owner_id')) {
                $table->dropForeign(['owner_id']);
                $table->dropColumn('owner_id');
            }

            if (Schema::hasColumn('tours', 'agency_id')) {
                $table->dropForeign(['agency_id']);
                $table->dropColumn('agency_id');
            }
        });
    }
};







