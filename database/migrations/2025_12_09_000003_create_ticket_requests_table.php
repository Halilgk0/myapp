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
        Schema::create('ticket_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tour_owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            
            // Ticket data (stored as JSON until approved)
            $table->string('voucher_no');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_nationality');
            $table->date('tour_date');
            $table->integer('adult_count')->default(0);
            $table->integer('child_count')->default(0);
            $table->integer('infant_count')->default(0);
            $table->decimal('adult_price', 10, 2)->default(0);
            $table->decimal('child_price', 10, 2)->default(0);
            $table->decimal('infant_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('currency', 5)->default('TRY');
            $table->string('pickup_location')->nullable();
            $table->string('room_number')->nullable();
            $table->text('passport_numbers')->nullable();
            
            // Status: pending, approved, rejected
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_requests');
    }
};


