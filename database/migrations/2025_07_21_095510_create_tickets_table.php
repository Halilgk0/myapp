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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->time('entry_time');
            $table->string('voucher_no')->nullable();
            $table->string('tracking_no')->unique();
            $table->date('tour_date');
            $table->time('pickup_time');
            $table->string('tour_country');
            $table->string('tour_region');
            $table->string('tour_name');
            $table->string('sales_agency');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('pickup_location');
            $table->string('room_number')->nullable();
            $table->text('passport_numbers')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit', 10, 2);
            $table->decimal('rest', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
