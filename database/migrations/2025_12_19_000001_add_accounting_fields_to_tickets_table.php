<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('accounted_at')->nullable()->after('infant_price');
            $table->foreignId('accounting_transaction_id')
                ->nullable()
                ->after('accounted_at')
                ->constrained('transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accounting_transaction_id');
            $table->dropColumn('accounting_transaction_id');
            $table->dropColumn('accounted_at');
        });
    }
};




































