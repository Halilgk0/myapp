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
        Schema::create('driver_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type'); // vehicle_assigned, vehicle_unassigned, login, logout, location_update, trip_start, trip_end
            $table->text('description');
            $table->json('metadata')->nullable(); // Additional data like vehicle plate, coordinates, etc.
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['driver_id', 'recorded_at']);
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_activities');
    }
}; 