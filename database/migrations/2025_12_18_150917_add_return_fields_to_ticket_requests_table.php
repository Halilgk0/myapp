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
            $table->text('return_reason')->nullable()->after('rejection_reason');
            $table->timestamp('returned_at')->nullable()->after('responded_at');
            $table->integer('return_count')->default(0)->after('returned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_requests', function (Blueprint $table) {
            $table->dropColumn(['return_reason', 'returned_at', 'return_count']);
        });
    }
};
