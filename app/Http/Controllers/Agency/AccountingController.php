<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\SettlementRequest;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /**
     * TCMB döviz kurları (USD, EUR, GBP, RUB)
     * Admin tarafındaki ile aynı, 5 dakika cache'li.
     */
    protected function getTcmbTop3Rates(): array
    {
        try {
            return \Cache::remember('tcmb_rates_top3', 300, function () {
                return $this->fetchTcmbTop3Rates();
            });
        } catch (\Throwable $e) {
            \Log::warning('Agency TCMB cache write/read failed, using uncached flow: ' . $e->getMessage());
            return $this->fetchTcmbTop3Rates();
        }
    }

    protected function fetchTcmbTop3Rates(): array
    {
            try {
                $http = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withoutVerifying()
                    ->withHeaders([
                        'Cache-Control' => 'no-cache',
                        'Pragma' => 'no-cache',
                        'Accept' => 'application/xml',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ]);

                $wanted = ['USD', 'EUR', 'GBP', 'RUB'];
                $nameMap = [
                    'USD' => 'ABD Doları',
                    'EUR' => 'Euro',
                    'GBP' => 'İngiliz Sterlini',
                    'RUB' => 'Rus Rublesi',
                ];

                $parse = function (string $xmlBody) use ($wanted, $nameMap) {
                    $xml = @simplexml_load_string($xmlBody);
                    if (!$xml) {
                        throw new \RuntimeException('Invalid XML');
                    }
                    $out = [];
                    foreach ($xml->Currency as $cur) {
                        $code = (string) $cur['CurrencyCode'];
                        if (!in_array($code, $wanted, true)) {
                            continue;
                        }
                        $name = $nameMap[$code] ?? ((string) $cur->Isim ?: (string) $cur->CurrencyName);
                        $buyStr = (string) $cur->ForexBuying;
                        $sellStr = (string) $cur->ForexSelling;
                        if ($buyStr === '') {
                            $buyStr = (string) $cur->BanknoteBuying;
                        }
                        if ($sellStr === '') {
                            $sellStr = (string) $cur->BanknoteSelling;
                        }
                        $buy = (float) str_replace(',', '.', $buyStr);
                        $sell = (float) str_replace(',', '.', $sellStr);
                        $out[] = [
                            'code' => $code,
                            'name' => $name,
                            'buy' => $buy,
                            'sell' => $sell,
                        ];
                    }
                    $date = (string)($xml['Tarih'] ?? '');
                    return ['date' => $date, 'items' => $out];
                };

                $resp = $http->get('https://www.tcmb.gov.tr/kurlar/today.xml', ['_ts' => time()]);
                if ($resp->ok()) {
                    $data = $parse($resp->body());
                    $istanbulToday = now('Europe/Istanbul')->format('d.m.Y');
                    if (!empty($data['date']) && $data['date'] === $istanbulToday && !empty($data['items'])) {
                        return $data + ['source' => 'TCMB'];
                    }
                    $candidate = $data;
                    $d = now('Europe/Istanbul');
                    for ($i = 0; $i < 3; $i++) {
                        $ym = $d->format('Ym');
                        $dmy = $d->format('dmY');
                        $url = "https://www.tcmb.gov.tr/kurlar/{$ym}/{$dmy}.xml";
                        $r2 = $http->get($url, ['_ts' => time()]);
                        if ($r2->ok()) {
                            $data2 = $parse($r2->body());
                            if (!empty($data2['items'])) {
                                return $data2 + ['source' => 'TCMB'];
                            }
                        }
                        $d = $d->subDay();
                    }
                    if (!empty($candidate['items'])) {
                        return $candidate + ['source' => 'TCMB'];
                    }
                }

                throw new \RuntimeException('TCMB unavailable');
            } catch (\Throwable $e) {
                \Log::warning('TCMB API hatası: ' . $e->getMessage());

                return [
                    'date' => now('Europe/Istanbul')->format('d.m.Y'),
                    'items' => [
                        ['code' => 'USD', 'name' => 'ABD Doları', 'buy' => 34.50, 'sell' => 34.55],
                        ['code' => 'EUR', 'name' => 'Euro', 'buy' => 37.50, 'sell' => 37.55],
                        ['code' => 'GBP', 'name' => 'İngiliz Sterlini', 'buy' => 43.50, 'sell' => 43.55],
                        ['code' => 'RUB', 'name' => 'Rus Rublesi', 'buy' => 0.38, 'sell' => 0.39],
                    ],
                    'fallback' => true,
                ];
            }
        
    }

    /**
     * Street agency can only see their own işlemler: kendi oluşturduğu
     * kayıtlar veya kendisinin oluşturduğu biletlerden üretilen otomatik kayıtlar.
     */
    protected function scopedQuery(int $userId)
    {
        // Sadece acentaya ait kayıtlar: kendi oluşturdukları veya kendi oluşturduğu
        // biletlerden üretilen gelir/giderler (satış + tur sahibine ödeme).
        return Transaction::where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
                ->orWhereIn('id', function ($sub) use ($userId) {
                    $sub->select('accounting_agency_income_transaction_id')
                        ->from('tickets')
                        ->where('created_by_user_id', $userId)
                        ->whereNotNull('accounting_agency_income_transaction_id');
                })
                ->orWhereIn('id', function ($sub) use ($userId) {
                    $sub->select('accounting_agency_payout_transaction_id')
                        ->from('tickets')
                        ->where('created_by_user_id', $userId)
                        ->whereNotNull('accounting_agency_payout_transaction_id');
                });
        })->where(function ($q) {
            $q->whereNull('payment_method')
                ->orWhere('payment_method', '!=', 'settlement-adjustment');
        });
    }

    public function index()
    {
        $userId = auth()->id();
        $allowedPerPage = [15, 25, 50, 100];
        $perPage = (int) request('per_page', 15);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        // Ensure direct DB date changes reflect immediately in agency accounting.
        Ticket::processExpiredUnaccounted($userId, 300);

        $q = $this->scopedQuery($userId);

        if (request()->filled('search')) {
            $q->whereMatchesAccountingSearch(request('search'));
        }
        if ($type = request('type')) {
            $q->where('type', $type);
        }
        if ($status = request('status')) {
            $q->where('status', $status);
        }
        if ($currency = request('currency')) {
            $q->where('currency', $currency);
        }
        if ($paymentMethod = request('payment_method')) {
            $q->where('payment_method', $paymentMethod);
        }
        if ($from = request('from')) {
            $q->whereDate('transaction_date', '>=', $from);
        }
        if ($to = request('to')) {
            $q->whereDate('transaction_date', '<=', $to);
        }

        $transactions = $q->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends(request()->query());
        Transaction::attachLinkedTickets($transactions->getCollection());

        // Totals within current filters — exclude settled
        $totals = [
            'income' => (clone $q)->where('type', 'income')->where('is_settled', false)->sum('amount'),
            'expense' => (clone $q)->where('type', 'expense')->where('is_settled', false)->sum('amount'),
        ];

        // Üst para birimi kartları: aktif filtrelerle uyumlu — exclude settled
        $currencyRows = (clone $q)->where('is_settled', false)->reorder()
            ->selectRaw('currency, type, SUM(amount) as total')
            ->groupBy('currency', 'type')
            ->get();
        $currencySummary = [];
        foreach ($currencyRows as $row) {
            $code = strtoupper($row->currency ?? 'TRY');
            $currencySummary[$code] = $currencySummary[$code] ?? ['income' => 0, 'expense' => 0, 'net' => 0];
            if ($row->type === 'income') {
                $currencySummary[$code]['income'] = (float) $row->total;
            } else {
                $currencySummary[$code]['expense'] = (float) $row->total;
            }
            $currencySummary[$code]['net'] = $currencySummary[$code]['income'] - $currencySummary[$code]['expense'];
        }

        $filterPaymentMethods = $this->scopedQuery($userId)
            ->whereNotNull('payment_method')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->values();

        $filterCurrencies = $this->scopedQuery($userId)
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency')
            ->values();

        // Stats for dashboard cards (genel, filtre dışı) — exclude settled
        $base = $this->scopedQuery($userId)->where('is_settled', false);
        $stats = [
            'this_month_income' => (clone $base)->where('type', 'income')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'this_month_expense' => (clone $base)->where('type', 'expense')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'last_month_income' => (clone $base)->where('type', 'income')
                ->whereYear('transaction_date', now()->subMonth()->year)
                ->whereMonth('transaction_date', now()->subMonth()->month)
                ->sum('amount'),
            'last_month_expense' => (clone $base)->where('type', 'expense')
                ->whereYear('transaction_date', now()->subMonth()->year)
                ->whereMonth('transaction_date', now()->subMonth()->month)
                ->sum('amount'),
            'pending_income' => (clone $base)->where('type', 'income')
                ->where('status', 'pending')
                ->sum('amount'),
            'pending_expense' => (clone $base)->where('type', 'expense')
                ->where('status', 'pending')
                ->sum('amount'),
            'total_income' => (clone $base)->where('type', 'income')->sum('amount'),
            'total_expense' => (clone $base)->where('type', 'expense')->sum('amount'),
        ];

        $stats['this_month_profit'] = $stats['this_month_income'] - $stats['this_month_expense'];
        $stats['last_month_profit'] = $stats['last_month_income'] - $stats['last_month_expense'];
        $stats['total_profit'] = $stats['total_income'] - $stats['total_expense'];

        $exchangeRates = $this->getTcmbTop3Rates();
        $pendingSettlements = SettlementRequest::where('agency_user_id', $userId)
            ->where('status', SettlementRequest::STATUS_PENDING)
            ->latest('created_at')
            ->get();

        return view('agency.accounting.index', compact(
            'transactions',
            'totals',
            'stats',
            'exchangeRates',
            'currencySummary',
            'pendingSettlements',
            'filterPaymentMethods',
            'filterCurrencies',
            'allowedPerPage'
        ));
    }

    /**
     * Live rates endpoint for agency panel.
     */
    public function rates()
    {
        return response()->json($this->getTcmbTop3Rates())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * AJAX endpoint for currency chart data (scoped to agency).
     */
    public function chartData(Request $request)
    {
        $userId = auth()->id();
        $currency = $request->input('currency', 'TRY');
        $period = $request->input('period', '12months');
        
        $incomeData = [];
        $expenseData = [];
        $labels = [];

        $baseChart = fn () => $this->scopedQuery($userId)->where('currency', $currency)->where('is_settled', false);
        
        switch ($period) {
            case '7days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->translatedFormat('d M');
                    $incomeData[] = (float) $baseChart()->where('type', 'income')->whereDate('transaction_date', $date->toDateString())->sum('amount');
                    $expenseData[] = (float) $baseChart()->where('type', 'expense')->whereDate('transaction_date', $date->toDateString())->sum('amount');
                }
                break;
                
            case '30days':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->translatedFormat('d M');
                    $incomeData[] = (float) $baseChart()->where('type', 'income')->whereDate('transaction_date', $date->toDateString())->sum('amount');
                    $expenseData[] = (float) $baseChart()->where('type', 'expense')->whereDate('transaction_date', $date->toDateString())->sum('amount');
                }
                break;
                
            case '3months':
                for ($i = 11; $i >= 0; $i--) {
                    $weekStart = now()->subWeeks($i)->startOfWeek();
                    $weekEnd = now()->subWeeks($i)->endOfWeek();
                    $labels[] = $weekStart->translatedFormat('d M');
                    $incomeData[] = (float) $baseChart()->where('type', 'income')->whereBetween('transaction_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->sum('amount');
                    $expenseData[] = (float) $baseChart()->where('type', 'expense')->whereBetween('transaction_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->sum('amount');
                }
                break;
                
            case '6months':
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $labels[] = $date->translatedFormat('M Y');
                    $incomeData[] = (float) $baseChart()->where('type', 'income')->whereYear('transaction_date', $date->year)->whereMonth('transaction_date', $date->month)->sum('amount');
                    $expenseData[] = (float) $baseChart()->where('type', 'expense')->whereYear('transaction_date', $date->year)->whereMonth('transaction_date', $date->month)->sum('amount');
                }
                break;
                
            case '12months':
            default:
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $labels[] = $date->translatedFormat('M Y');
                    $incomeData[] = (float) $baseChart()->where('type', 'income')->whereYear('transaction_date', $date->year)->whereMonth('transaction_date', $date->month)->sum('amount');
                    $expenseData[] = (float) $baseChart()->where('type', 'expense')->whereYear('transaction_date', $date->year)->whereMonth('transaction_date', $date->month)->sum('amount');
                }
                break;
        }
        
        return response()->json([
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
            'currency' => $currency,
            'period' => $period,
        ]);
    }

    /**
     * Sokak acentası: kendi oluşturduğu biletler için mutabakat (Hesap Gör).
     * Admin onayı beklenmez; kasa düşümü hemen uygulanır (admin panelindeki "kendi satış" ile aynı mantık).
     */
    public function submitSettlement(Request $request)
    {
        $userId = (int) auth()->id();

        $data = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
        ]);

        $ticketIds = collect($data['ticket_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $ownedCount = Ticket::whereIn('id', $ticketIds)
            ->where('created_by_user_id', $userId)
            ->count();

        if ($ownedCount !== count($ticketIds)) {
            return response()->json([
                'message' => 'Sadece kendi oluşturduğunuz biletler mutabakata dahil edilebilir.',
            ], 422);
        }

        $settled = (int) DB::transaction(
            fn () => SettlementRequest::applyTicketSettlementRemovals($ticketIds)
        );

        if ($settled === 0) {
            return response()->json([
                'message' => 'Seçili biletlerde düşülecek muhasebe kalemi bulunamadı.',
            ], 422);
        }

        return response()->json([
            'message' => 'Mutabakat tamamlandı; seçili kayıtlar kasanızdan düşüldü.',
            'immediate_settlement_applied' => true,
            'immediate_transactions_settled' => $settled,
        ]);
    }

    public function approveSettlement(SettlementRequest $settlementRequest)
    {
        $userId = (int) auth()->id();
        if ((int) $settlementRequest->agency_user_id !== $userId) {
            abort(403, 'Bu mutabakatı onaylama yetkiniz yok.');
        }

        if ($settlementRequest->status !== SettlementRequest::STATUS_PENDING) {
            return back()->with('error', 'Bu mutabakat kaydı zaten sonuçlandırılmış.');
        }

        DB::transaction(function () use ($settlementRequest) {
            $ticketIds = collect($settlementRequest->ticket_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            SettlementRequest::applyTicketSettlementRemovals(
                $ticketIds,
                $settlementRequest->transaction_ids ?? null
            );

            $settlementRequest->update([
                'status' => SettlementRequest::STATUS_APPROVED,
                'approved_by_agency_at' => now(),
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', 'Mutabakat onaylandı. Düşüm işlemleri kasaya işlendi.');
    }

    public function rejectSettlement(SettlementRequest $settlementRequest)
    {
        $userId = (int) auth()->id();
        if ((int) $settlementRequest->agency_user_id !== $userId) {
            abort(403, 'Bu mutabakatı reddetme yetkiniz yok.');
        }

        if ($settlementRequest->status !== SettlementRequest::STATUS_PENDING) {
            return back()->with('error', 'Bu mutabakat kaydı zaten sonuçlandırılmış.');
        }

        $settlementRequest->update([
            'status' => SettlementRequest::STATUS_REJECTED,
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Mutabakat kaydı reddedildi.');
    }
}


