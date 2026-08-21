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
        if (!Schema::hasTable('tours')) {
            Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('country');
            $table->string('region');
            $table->integer('duration_days')->default(0);
            $table->integer('duration_hours')->default(0);
            $table->time('pickup_time')->nullable();
            $table->time('dropoff_time')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->decimal('price_adult', 10, 2);
            $table->decimal('price_child', 10, 2)->default(0);
            $table->decimal('price_infant', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->integer('max_capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            
            // Tarih seçimi için alanlar
            $table->json('available_days')->nullable(); // [1,2,3,4,5,6,7] (Pazartesi=1, Pazar=7)
            $table->json('available_months')->nullable(); // [1,2,3,4,5,6,7,8,9,10,11,12]
            $table->json('available_years')->nullable(); // [2024,2025,2026]
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
}; 