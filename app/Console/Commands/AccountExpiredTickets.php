<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Tour;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccountExpiredTickets extends Command
{
    protected $signature = 'tickets:account-expired';

    protected $description = 'Tarihi geçen biletleri gelir olarak muhasebeye ekle';

    /**
     * Tur sahibinin taban fiyatına göre gelir payını hesaplar.
     * Eğer bilet üzerinde daha önce hesaplanmış değer varsa onu kullanır.
     */
    protected function calculateOwnerShare(Ticket $ticket): array
    {
        if ($ticket->owner_share_amount && $ticket->owner_share_currency) {
            return [$ticket->owner_share_amount, $ticket->owner_share_currency];
        }

        $tour = $ticket->tour ?: Tour::find($ticket->tour_id);
        $currency = strtoupper($ticket->base_currency ?? ($tour->currency ?? $ticket->currency ?? 'TRY'));

        // Taban payda oncelik her zaman biletin kendi fiyatlaridir.
        $prices = [
            'adult' => (float) ($ticket->adult_price ?? 0),
            'child' => (float) ($ticket->child_price ?? 0),
            'infant' => (float) ($ticket->infant_price ?? 0),
        ];

        // Eski kayitlar icin birim fiyatlar yoksa tur fiyatina geri dus.
        if (($prices['adult'] + $prices['child'] + $prices['infant']) <= 0 && $tour) {
            $date = $ticket->tour_date ?? Carbon::today();
            $fallbackPrices = $tour->getPricesForDate($date);
            if (is_array($fallbackPrices)) {
                $prices = [
                    'adult' => (float) ($fallbackPrices['adult'] ?? 0),
                    'child' => (float) ($fallbackPrices['child'] ?? 0),
                    'infant' => (float) ($fallbackPrices['infant'] ?? 0),
                ];
            }
        }

        $adult = $ticket->adult_count ?? 0;
        $child = $ticket->child_count ?? 0;
        $infant = $ticket->infant_count ?? 0;

        $amount = ($adult * ($prices['adult'] ?? 0))
            + ($child * ($prices['child'] ?? 0))
            + ($infant * ($prices['infant'] ?? 0));

        if ($amount <= 0) {
            // Fallback for legacy/manual records.
            $saleTotal = (float) ($ticket->total_price ?? 0);
            $restBase = (float) ($ticket->rest_converted_amount ?? 0);
            if ($restBase <= 0) {
                $restBase = (float) ($ticket->rest_adjustment_amount ?? 0);
            }
            $amount = max($saleTotal - $restBase, 0);
        }

        return [$amount, $currency];
    }

    protected function calculateAgencyNetSaleAmount(Ticket $ticket): float
    {
        $saleTotal = (float) ($ticket->total_price ?? 0);
        $saleCurrency = strtoupper($ticket->sale_currency ?? $ticket->currency ?? 'TRY');
        $restAmount = (float) ($ticket->rest_adjustment_amount ?? 0);

        if ($saleTotal <= 0 || $restAmount <= 0) {
            return max($saleTotal, 0);
        }

        $restCurrency = strtoupper($ticket->rest_adjustment_currency ?? $saleCurrency);
        $restInSaleCurrency = $restAmount;

        if ($restCurrency !== $saleCurrency) {
            $fxRate = (float) ($ticket->rest_fx_rate ?? 0);
            $fxSource = strtoupper($ticket->rest_fx_source_currency ?? '');
            $fxTarget = strtoupper($ticket->rest_fx_target_currency ?? '');
            $baseCurrency = strtoupper($ticket->base_currency ?? $saleCurrency);

            if ($ticket->rest_converted_amount !== null && $baseCurrency === $saleCurrency) {
                $restInSaleCurrency = (float) $ticket->rest_converted_amount;
            } elseif ($fxRate > 0 && $fxSource === $restCurrency && $fxTarget === $saleCurrency) {
                $restInSaleCurrency = $restAmount * $fxRate;
            } elseif ($fxRate > 0 && $fxSource === $saleCurrency && $fxTarget === $restCurrency) {
                $restInSaleCurrency = $restAmount / $fxRate;
            }
        }

        return max(round($saleTotal - $restInSaleCurrency, 2), 0);
    }

    public function handle(): int
    {
        $today = Carbon::today();

        $tickets = Ticket::whereDate('tour_date', '<', $today)
            ->whereNull('accounted_at')
            ->orderBy('tour_date')
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('İşlenecek tarihi geçmiş bilet bulunamadı.');
            return self::SUCCESS;
        }

        $created = 0;

        foreach ($tickets as $ticket) {
            DB::transaction(function () use ($ticket, &$created) {
                if ($ticket->accounted_at) {
                    return;
                }

                [$ownerShareAmount, $ownerCurrency] = $this->calculateOwnerShare($ticket);
                $ownerShareAmount = (float) $ownerShareAmount;
                $ownerCurrency = strtoupper($ownerCurrency ?? 'TRY');
                $restAmount = (float) ($ticket->rest_adjustment_amount ?? 0);
                $restCurrency = strtoupper($ticket->rest_adjustment_currency ?? $ownerCurrency);

                $creator = $ticket->created_by_user_id ? User::find($ticket->created_by_user_id) : null;
                $tourOwnerId = optional($ticket->tour)->owner_id;
                $isAgency = $creator && $creator->isAgency();
                $ticketCurrency = strtoupper($ticket->currency ?? 'TRY');

                $titleBase = sprintf('Bilet - %s (%s)', $ticket->tour_name ?? 'Tur', optional($ticket->tour_date)->format('d.m.Y'));

                if ($isAgency) {
                    // 1) Sokak acentası satışı (müşteriden tahsilat) - gelir
                    if (!$ticket->accounting_agency_income_transaction_id) {
                        $agencyNetSale = $this->calculateAgencyNetSaleAmount($ticket);
                        $agencyIncome = Transaction::create([
                            'type' => 'income',
                            'title' => $titleBase . ' Satış Geliri',
                            'amount' => $agencyNetSale,
                            'currency' => $ticketCurrency,
                            'transaction_date' => $ticket->tour_date ?? now()->toDateString(),
                            'payment_method' => 'sale-ticket',
                            'status' => 'paid',
                            'notes' => sprintf(
                                "Otomatik eklendi (tarihi geçen bilet)\nTracking: %s\nVoucher: %s",
                                $ticket->tracking_no ?? '-',
                                $ticket->voucher_no ?? '-'
                            ),
                            'created_by' => $creator->id,
                        ]);
                        $ticket->accounting_agency_income_transaction_id = $agencyIncome->id;
                    }

                    if ($tourOwnerId) {
                        // 2) Sokak acentasından tur sahibine ödeme - gider
                        if ($ownerShareAmount > 0 && !$ticket->accounting_agency_payout_transaction_id) {
                            $agencyPayout = Transaction::create([
                                'type' => 'expense',
                                'title' => $titleBase . ' Tur Sahibi Payı',
                                'amount' => $ownerShareAmount,
                                'currency' => $ownerCurrency,
                                'transaction_date' => $ticket->tour_date ?? now()->toDateString(),
                                'payment_method' => 'payout-owner',
                                'status' => 'paid',
                                'notes' => sprintf(
                                    "Otomatik eklendi (tarihi geçen bilet) | Tur Sahibi: %s | Tracking: %s",
                                    $ticket->tour?->owner?->name ?? '-',
                                    $ticket->tracking_no ?? '-'
                                ),
                                'created_by' => $creator->id,
                            ]);
                            $ticket->accounting_agency_payout_transaction_id = $agencyPayout->id;
                        }

                        // 3) Tur sahibi için gelir kaydı
                        if ($ownerShareAmount > 0 && !$ticket->accounting_owner_transaction_id) {
                            $ownerIncome = Transaction::create([
                                'type' => 'income',
                                'title' => $titleBase . ' (Acenta Satışı)',
                                'amount' => $ownerShareAmount,
                                'currency' => $ownerCurrency,
                                'transaction_date' => $ticket->tour_date ?? now()->toDateString(),
                                'payment_method' => 'owner-share',
                                'status' => 'paid',
                                'notes' => sprintf(
                                    "Sokak acentası satışı. Tracking: %s | Acenta: %s",
                                    $ticket->tracking_no ?? '-',
                                    $creator->name ?? '-'
                                ),
                                'created_by' => $tourOwnerId,
                            ]);
                            $ticket->accounting_owner_transaction_id = $ownerIncome->id;
                            // Backward compatibility
                            $ticket->accounting_transaction_id = $ownerIncome->id;
                        }

                        // 4) Rest geliri (admin payı ek gelir) - Sokak acentası satışından
                        if ($restAmount > 0 && !$ticket->accounting_rest_transaction_id) {
                            $restIncome = Transaction::create([
                                'type' => 'income',
                                'title' => $titleBase . ' Rest Geliri (Acenta: ' . ($creator->name ?? 'Bilinmeyen') . ')',
                                'amount' => $restAmount,
                                'currency' => $restCurrency,
                                'transaction_date' => $ticket->tour_date ?? now()->toDateString(),
                                'payment_method' => 'rest-adjustment',
                                'status' => 'paid',
                                'notes' => sprintf(
                                    "Sokak acentası satışından rest geliri. Tracking: %s | Acenta: %s",
                                    $ticket->tracking_no ?? '-',
                                    $creator->name ?? '-'
                                ),
                                'created_by' => $tourOwnerId,
                            ]);
                            $ticket->accounting_rest_transaction_id = $restIncome->id;
                        }
                    }
                } else {
                    // Mevcut senaryo: tek taraflı gelir kaydı (kendi satışımız)
                    if (!$ticket->accounting_owner_transaction_id) {
                        $transaction = Transaction::create([
                            'type' => 'income',
                            'title' => $titleBase . ' (Kendi Satış)',
                            'amount' => (float) ($ticket->total_price ?? 0),
                            'currency' => $ticketCurrency,
                            'transaction_date' => $ticket->tour_date ?? now()->toDateString(),
                            'payment_method' => 'auto-expired-ticket',
                            'status' => 'paid',
                            'notes' => sprintf(
                                "Otomatik eklendi (tarihi geçen bilet)\nTracking: %s\nVoucher: %s",
                                $ticket->tracking_no ?? '-',
                                $ticket->voucher_no ?? '-'
                            ),
                            'created_by' => $tourOwnerId ?: $ticket->created_by_user_id,
                        ]);
                        $ticket->accounting_owner_transaction_id = $transaction->id;
                        $ticket->accounting_transaction_id = $transaction->id;
                    }
                }

                // Pay bilgilerini kaydet
                $ticket->owner_share_amount = $ownerShareAmount;
                $ticket->owner_share_currency = $ownerCurrency;
                $ticket->accounted_at = now();
                $ticket->save();

                $created++;
            });
        }

        $this->info("{$created} bilet muhasebeye gelir olarak eklendi.");

        return self::SUCCESS;
    }
}


