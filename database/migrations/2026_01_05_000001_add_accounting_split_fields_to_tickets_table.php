<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('owner_share_amount', 12, 2)->nullable()->after('accounting_transaction_id');
            $table->string('owner_share_currency', 5)->nullable()->after('owner_share_amount');

            $table->unsignedBigInteger('accounting_owner_transaction_id')->nullable()->after('owner_share_currency');
            $table->unsignedBigInteger('accounting_agency_income_transaction_id')->nullable()->after('accounting_owner_transaction_id');
            $table->unsignedBigInteger('accounting_agency_payout_transaction_id')->nullable()->after('accounting_agency_income_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'owner_share_amount',
                'owner_share_currency',
                'accounting_owner_transaction_id',
                'accounting_agency_income_transaction_id',
                'accounting_agency_payout_transaction_id',
            ]);
        });
    }
};




































