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
        Schema::table('users', function (Blueprint $table) {
            // Remove device_id column if it exists
            if (Schema::hasColumn('users', 'device_id')) {
                $table->dropForeign(['device_id']);
                $table->dropColumn('device_id');
            }
            
            // Add phone_number column if it doesn't exist
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back device_id column
            if (!Schema::hasColumn('users', 'device_id')) {
                $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null');
            }
            
            // Remove phone_number column
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropColumn('phone_number');
            }
        });
    }
};
