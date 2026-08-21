<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id');
            $table->unsignedBigInteger('agency_user_id');
            $table->json('transaction_ids');
            $table->string('status', 20)->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('approved_by_agency_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['agency_user_id', 'status']);
            $table->index(['admin_user_id', 'status']);
            $table->foreign('admin_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('agency_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_requests');
    }
};

