<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('salary_amount', 12, 2)->default(0)->after('supported_nationalities');
            $table->string('salary_currency', 3)->default('TRY')->after('salary_amount');
            $table->tinyInteger('salary_day')->default(1)->after('salary_currency'); // 1-28 arası
            $table->timestamp('last_salary_paid_at')->nullable()->after('salary_day');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['salary_amount', 'salary_currency', 'salary_day', 'last_salary_paid_at']);
        });
    }
};




































