<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PayDriverSalaries extends Command
{
    protected $signature = 'drivers:pay-salaries';

    protected $description = 'Şoför maaşlarını maaş günü geldiğinde otomatik gider olarak ekler';

    public function handle(): int
    {
        $today = Carbon::now('Europe/Istanbul');
        $day = (int) $today->day;

        $drivers = User::where('level', User::LEVEL_DRIVER)
            ->where('salary_amount', '>', 0)
            ->where('salary_day', $day)
            ->get();

        if ($drivers->isEmpty()) {
            $this->info('Ödenecek şoför maaşı bulunamadı.');
            return self::SUCCESS;
        }

        $paid = 0;

        foreach ($drivers as $driver) {
            // Aynı ay içinde zaten ödendiyse geç
            if ($driver->last_salary_paid_at &&
                $driver->last_salary_paid_at->isSameMonth($today) &&
                $driver->last_salary_paid_at->isSameYear($today)) {
                continue;
            }

            DB::transaction(function () use ($driver, $today, &$paid) {
                $currency = strtoupper($driver->salary_currency ?? 'TRY');
                $amount = $driver->salary_amount ?? 0;

                $title = sprintf(
                    'Şoför Maaşı - %s (%s %s)',
                    $driver->name,
                    $today->translatedFormat('F'),
                    $today->year
                );

                $transaction = Transaction::create([
                    'type' => 'expense',
                    'title' => $title,
                    'amount' => $amount,
                    'currency' => $currency,
                    'transaction_date' => $today->toDateString(),
                    'payment_method' => 'salary-auto',
                    'status' => 'paid',
                    'notes' => 'Otomatik maaş ödemesi',
                    'created_by' => null,
                ]);

                $driver->last_salary_paid_at = $today;
                $driver->save();

                $paid++;
            });
        }

        $this->info("{$paid} şoför maaşı gider olarak eklendi.");

        return self::SUCCESS;
    }
}





































