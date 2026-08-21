<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'accounting_rest_transaction_id')) {
                $table->unsignedBigInteger('accounting_rest_transaction_id')->nullable()->after('accounting_agency_payout_transaction_id');
                $table->foreign('accounting_rest_transaction_id')
                    ->references('id')
                    ->on('transactions')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'accounting_rest_transaction_id')) {
                $table->dropForeign(['accounting_rest_transaction_id']);
                $table->dropColumn('accounting_rest_transaction_id');
            }
        });
    }
};

