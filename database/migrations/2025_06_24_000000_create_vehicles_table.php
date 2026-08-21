<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('vehicle_type')->default('Minibus'); // minibus, bus, car, etc.
            $table->string('color')->nullable();
            $table->integer('capacity')->default(12);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
