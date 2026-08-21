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
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('license_number')->unique()->nullable();
            $table->date('license_expiry')->nullable();
            $table->json('supported_nationalities')->nullable(); // Desteklenen milliyetler
            $table->string('status')->default('Aktif'); // Aktif, İzinli, Servis Dışı
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};