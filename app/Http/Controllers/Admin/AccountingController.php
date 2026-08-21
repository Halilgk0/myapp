<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Ticket;
use App\Models\SettlementRequest;
use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AccountingController extends Controller
{
    protected function resolveAgencyFilterId(Request $request): ?int
    {
        $lockedAgencyId = (int) $request->input('locked_agency_id', 0);
        if ($lockedAgencyId > 0) {
            return $lockedAgencyId;
        }

        $agencyId = (int) $request->input('agency_id', 0);
        return $agencyId > 0 ? $agencyId : null;
    }

    protected function applyAgencyFilter($query, ?int $agencyId)
    {
        if (!$agencyId) {
            return $query;
        }

        return $query->whereExists(function ($sub) use ($agencyId) {
            $sub->select(DB::raw(1))
                ->from('tickets')
                ->where('tickets.created_by_user_id', $agencyId)
                ->where(function ($match) {
                    $match->whereColumn('tickets.accounting_transaction_id', 'transactions.id')
                        ->orWhereColumn('tickets.accounting_owner_transaction_id', 'transactions.id')
                        ->orWhereColumn('tickets.accounting_rest_transaction_id', 'transactions.id')
                        ->orWhereColumn('tickets.accounting_agency_income_transaction_id', 'transactions.id')
                        ->orWhereColumn('tickets.accounting_agency_payout_transaction_id', 'transactions.id');
                });
        });
    }

    /**
     * Fetch TCMB Döviz Alış/Satış (ForexBuying/ForexSelling) for USD, EUR, GBP.
     * Robust against stale/cached responses by trying date-specific XML when needed.
     * Returns TRY per 1 unit for both buying and selling.
     */
    protected function getTcmbTop3Rates(): array
    {
        // Cache for 5 minutes to avoid hammering provider.
        // If file cache storage is unavailable on the host, continue without cache.
        try {
            return \Cache::remember('tcmb_rates_top3', 300, function () {
                return $this->fetchTcmbTop3Rates();
            });
        } catch (\Throwable $e) {
            \Log::warning('TCMB cache write/read failed, using uncached flow: ' . $e->getMessage());
            return $this->fetchTcmbTop3Rates();
        }
    }

    protected function fetchTcmbTop3Rates(): array
    {
            try {
                $http = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withoutVerifying() // SSL sertifika doğrulamasını atla
                    ->withHeaders([
                        'Cache-Control' => 'no-cache',
                        'Pragma' => 'no-cache',
                        'Accept' => 'application/xml',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ]);

                $wanted = ['USD','EUR','GBP','RUB'];
                $nameMap = [
                    'USD' => 'ABD Doları',
                    'EUR' => 'Euro',
                    'GBP' => 'İngiliz Sterlini',
                    'RUB' => 'Rus Rublesi',
                ];

                $parse = function (string $xmlBody) use ($wanted, $nameMap) {
                    $xml = @simplexml_load_string($xmlBody);
                    if (!$xml) { throw new \RuntimeException('Invalid XML'); }
                    $out = [];
                    foreach ($xml->Currency as $cur) {
                        $code = (string) $cur['CurrencyCode'];
                        if (!in_array($code, $wanted, true)) continue;
                        $name = $nameMap[$code] ?? ((string) $cur->Isim ?: (string) $cur->CurrencyName);
                        $buyStr = (string) $cur->ForexBuying;
                        $sellStr = (string) $cur->ForexSelling;
                        // Fallback to Banknote if Forex empty
                        if ($buyStr === '') { $buyStr = (string) $cur->BanknoteBuying; }
                        if ($sellStr === '') { $sellStr = (string) $cur->BanknoteSelling; }
                        $buy = (float) str_replace(',', '.', $buyStr);
                        $sell = (float) str_replace(',', '.', $sellStr);
                        $out[] = [
                            'code' => $code,
                            'name' => $name,
                            'buy' => $buy,
                            'sell' => $sell,
                        ];
                    }
                    $date = (string) ($xml['Tarih'] ?? '');
                    return ['date' => $date, 'items' => $out];
                };

                // Try today.xml first
                $resp = $http->get('https://www.tcmb.gov.tr/kurlar/today.xml', ['_ts' => time()]);
                if ($resp->ok()) {
                    $data = $parse($resp->body());
                    // If date matches Istanbul today, use it
                    $istanbulToday = now('Europe/Istanbul')->format('d.m.Y');
                    if (!empty($data['date']) && $data['date'] === $istanbulToday && !empty($data['items'])) {
                        return $data + ['source' => 'TCMB'];
                    }
                    $candidate = $data;

                    // Try date-specific up to 3 previous days
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
                
                // Fallback: Tahmini kurlar (günlük güncellenmeli)
                return [
                    'date' => now('Europe/Istanbul')->format('d.m.Y'),
                    'items' => [
                        [
                            'code' => 'USD',
                            'name' => 'ABD Doları',
                            'buy' => 34.50,
                            'sell' => 34.55,
                        ],
                        [
                            'code' => 'EUR',
                            'name' => 'Euro',
                            'buy' => 37.50,
                            'sell' => 37.55,
                        ],
                        [
                            'code' => 'GBP',
                            'name' => 'İngiliz Sterlini',
                            'buy' => 43.50,
                            'sell' => 43.55,
                        ],
                        [
                            'code' => 'RUB',
                            'name' => 'Rus Rublesi',
                            'buy' => 0.38,
                            'sell' => 0.39,
                        ],
                    ],
                    'fallback' => true,
                ];
            }
        
    }

    protected function scopedQuery()
    {
        $userId = auth()->id();
        // Yalnızca bu adminin veya sistemin (null) oluşturduğu işlemler
        return Transaction::where(function ($q) use ($userId) {
            $q->whereNull('created_by')
              ->orWhere('created_by', $userId);
        });
    }

    public function index()
    {
        $perPage = (int) request('per_page', 15);
        $selectedAgencyId = $this->resolveAgencyFilterId(request());
        // Ensure direct DB date changes reflect immediately in accounting.
        Ticket::processExpiredUnaccounted($selectedAgencyId, 300);
        $q = $this->scopedQuery();

        // Type filter
        if ($type = request('type')) {
            $q->where('type', $type);
        }
        // Status filter (including virtual 'settled' status)
        if ($status = request('status')) {
            if ($status === 'settled') {
                $q->where('is_settled', true);
            } else {
                $q->where('status', $status)->where('is_settled', false);
            }
        }
        // Currency filter
        if ($currency = request('currency')) {
            $q->where('currency', $currency);
        }
        // Payment method filter
        if ($paymentMethod = request('payment_method')) {
            $q->where('payment_method', $paymentMethod);
        }
        // Search: başlık veya bilet takip no
        if ($search = request('search')) {
            $q->whereMatchesAccountingSearch($search);
        }
        // Date range filters
        if ($from = request('from')) {
            $q->whereDate('transaction_date', '>=', $from);
        }
        if ($to = request('to')) {
            $q->whereDate('transaction_date', '<=', $to);
        }
        
        // Agency filter - filter by agency that created the ticket
        $this->applyAgencyFilter($q, $selectedAgencyId);

        $transactions = $q->latest('created_at')->paginate($perPage)->appends(request()->query());
        Transaction::attachLinkedTickets($transactions->getCollection());

        // Get unique payment methods for filter dropdown
        $paymentMethodsQuery = (clone $this->scopedQuery());
        $this->applyAgencyFilter($paymentMethodsQuery, $selectedAgencyId);
        $paymentMethods = $paymentMethodsQuery
            ->select('payment_method')
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method')
            ->toArray();

        // Get unique currencies for filter dropdown
        $currenciesQuery = (clone $this->scopedQuery());
        $this->applyAgencyFilter($currenciesQuery, $selectedAgencyId);
        $currencies = $currenciesQuery
            ->select('currency')
            ->distinct()
            ->pluck('currency')
            ->toArray();
        
        // Get agencies that have accounting transactions (via ticket relation)
        $agencyIds = Ticket::whereNotNull('created_by_user_id')
            ->where(function ($w) {
                $w->whereNotNull('accounting_transaction_id')
                    ->orWhereNotNull('accounting_owner_transaction_id')
                    ->orWhereNotNull('accounting_rest_transaction_id')
                    ->orWhereNotNull('accounting_agency_income_transaction_id')
                    ->orWhereNotNull('accounting_agency_payout_transaction_id');
            })
            ->pluck('created_by_user_id')
            ->unique();
        
        $agencies = User::whereIn('id', $agencyIds)
            ->where('level', User::LEVEL_AGENCY)
            ->orderBy('name')
            ->get(['id', 'name']);

        $totals = [
            'income' => (clone $q)->where('type', 'income')->where('is_settled', false)->sum('amount'),
            'expense' => (clone $q)->where('type', 'expense')->where('is_settled', false)->sum('amount'),
        ];

        // Currency breakdown (income/expense/net per currency) — exclude settled
        $currencyQuery = (clone $this->scopedQuery())->where('is_settled', false);
        $this->applyAgencyFilter($currencyQuery, $selectedAgencyId);
        $currencyRows = $currencyQuery
            ->selectRaw('currency, type, SUM(amount) as total')
            ->groupBy('currency', 'type')
            ->get();
        $currencySummary = [];
        foreach ($currencyRows as $row) {
            $code = strtoupper($row->currency ?? 'TRY');
            if (!isset($currencySummary[$code])) {
                $currencySummary[$code] = ['income' => 0, 'expense' => 0, 'net' => 0];
            }
            if ($row->type === 'income') {
                $currencySummary[$code]['income'] = (float) $row->total;
            } else {
                $currencySummary[$code]['expense'] = (float) $row->total;
            }
            $currencySummary[$code]['net'] = $currencySummary[$code]['income'] - $currencySummary[$code]['expense'];
        }

        // Statistics for dashboard cards — exclude settled from active totals
        $baseScoped = $this->scopedQuery()->where('is_settled', false);
        $this->applyAgencyFilter($baseScoped, $selectedAgencyId);
        $stats = [
            // This month
            'this_month_income' => (clone $baseScoped)->where('type', 'income')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'this_month_expense' => (clone $baseScoped)->where('type', 'expense')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            
            // Last month
            'last_month_income' => (clone $baseScoped)->where('type', 'income')
                ->whereYear('transaction_date', now()->subMonth()->year)
                ->whereMonth('transaction_date', now()->subMonth()->month)
                ->sum('amount'),
            'last_month_expense' => (clone $baseScoped)->where('type', 'expense')
                ->whereYear('transaction_date', now()->subMonth()->year)
                ->whereMonth('transaction_date', now()->subMonth()->month)
                ->sum('amount'),
            
            // Pending payments
            'pending_income' => (clone $baseScoped)->where('type', 'income')
                ->where('status', 'pending')
                ->sum('amount'),
            'pending_expense' => (clone $baseScoped)->where('type', 'expense')
                ->where('status', 'pending')
                ->sum('amount'),
            
            // All time
            'total_income' => (clone $baseScoped)->where('type', 'income')->sum('amount'),
            'total_expense' => (clone $baseScoped)->where('type', 'expense')->sum('amount'),
        ];

        $stats['this_month_profit'] = $stats['this_month_income'] - $stats['this_month_expense'];
        $stats['last_month_profit'] = $stats['last_month_income'] - $stats['last_month_expense'];
        $stats['total_profit'] = $stats['total_income'] - $stats['total_expense'];

        $selectedAgency = null;
        if ($selectedAgencyId) {
            $selectedAgency = User::where('level', User::LEVEL_AGENCY)
                ->whereKey($selectedAgencyId)
                ->first(['id', 'name']);
        }

        $exchangeRates = $this->getTcmbTop3Rates();

        return view('admin.accounting.index', compact(
            'transactions', 
            'totals', 
            'stats',
            'agencies',
            'selectedAgency',
            'exchangeRates', 
            'currencySummary',
            'paymentMethods',
            'currencies'
        ));
    }

    /**
     * Live rates endpoint for AJAX polling.
     */
    public function rates()
    {
        return response()->json($this->getTcmbTop3Rates())
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * AJAX endpoint for currency chart data.
     */
    public function chartData(Request $request)
    {
        $currency = $request->input('currency', 'TRY');
        $period = $request->input('period', '12months');
        $selectedAgencyId = $this->resolveAgencyFilterId($request);
        
        $incomeData = [];
        $expenseData = [];
        $labels = [];
        
        $baseQuery = $this->scopedQuery()->where('currency', $currency)->where('is_settled', false);
        $this->applyAgencyFilter($baseQuery, $selectedAgencyId);

        switch ($period) {
            case '7days':
                // Son 7 gün - günlük
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->translatedFormat('d M');
                    
                    $income = (clone $baseQuery)
                        ->where('type', 'income')
                        ->whereDate('transaction_date', $date->toDateString())
                        ->sum('amount');
                    
                    $expense = (clone $baseQuery)
                        ->where('type', 'expense')
                        ->whereDate('transaction_date', $date->toDateString())
                        ->sum('amount');
                    
                    $incomeData[] = (float) $income;
                    $expenseData[] = (float) $expense;
                }
                break;
                
            case '30days':
                // Son 30 gün - günlük
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->translatedFormat('d M');
                    
                    $income = (clone $baseQuery)
                        ->where('type', 'income')
                        ->whereDate('transaction_date', $date->toDateString())
                        ->sum('amount');
                    
                    $expense = (clone $baseQuery)
                        ->where('type', 'expense')
                        ->whereDate('transaction_date', $date->toDateString())
                        ->sum('amount');
                    
                    $incomeData[] = (float) $income;
                    $expenseData[] = (float) $expense;
                }
                break;
                
            case '3months':
                // Son 3 ay - haftalık
                for ($i = 11; $i >= 0; $i--) {
                    $endDate = now()->subWeeks($i);
                    $startDate = $endDate->copy()->subDays(6);
                    $labels[] = $startDate->translatedFormat('d M');
                    
                    $income = (clone $baseQuery)
                        ->where('type', 'income')
                        ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->sum('amount');
                    
                    $expense = (clone $baseQuery)
                        ->where('type', 'expense')
                        ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->sum('amount');
                    
                    $incomeData[] = (float) $income;
                    $expenseData[] = (float) $expense;
                }
                break;
                
            case '6months':
                // Son 6 ay - aylık
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $labels[] = $date->translatedFormat('M Y');
                    
                    $income = (clone $baseQuery)
                        ->where('type', 'income')
                        ->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->sum('amount');
                    
                    $expense = (clone $baseQuery)
                        ->where('type', 'expense')
                        ->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->sum('amount');
                    
                    $incomeData[] = (float) $income;
                    $expenseData[] = (float) $expense;
                }
                break;
                
            case '12months':
            default:
                // Son 12 ay - aylık
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $labels[] = $date->translatedFormat('M Y');
                    
                    $income = (clone $baseQuery)
                        ->where('type', 'income')
                        ->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->sum('amount');
                    
                    $expense = (clone $baseQuery)
                        ->where('type', 'expense')
                        ->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->sum('amount');
                    
                    $incomeData[] = (float) $income;
                    $expenseData[] = (float) $expense;
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
     * AJAX endpoint for live filtering transactions.
     */
    public function filter(Request $request)
    {
        $selectedAgencyId = $this->resolveAgencyFilterId($request);
        Ticket::processExpiredUnaccounted($selectedAgencyId, 300);

        $q = $this->scopedQuery();

        // Apply filters
        if ($search = $request->input('search')) {
            $q->whereMatchesAccountingSearch($search);
        }
        if ($type = $request->input('type')) {
            $q->where('type', $type);
        }
        if ($status = $request->input('status')) {
            if ($status === 'settled') {
                $q->where('is_settled', true);
            } else {
                $q->where('status', $status)->where('is_settled', false);
            }
        }
        if ($currency = $request->input('currency')) {
            $q->where('currency', $currency);
        }
        if ($paymentMethod = $request->input('payment_method')) {
            $q->where('payment_method', $paymentMethod);
        }
        if ($from = $request->input('from')) {
            $q->whereDate('transaction_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $q->whereDate('transaction_date', '<=', $to);
        }
        
        // Agency filter - filter by agency that created the ticket
        $this->applyAgencyFilter($q, $selectedAgencyId);

        // Get transactions; link tickets via any accounting FK for takip no / owner-share labels
        $transactions = (clone $q)
            ->latest('transaction_date')
            ->get();
        Transaction::attachLinkedTickets($transactions);

        // Calculate totals — exclude settled from active totals
        $totalIncome = $transactions->where('type', 'income')->where('is_settled', false)->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->where('is_settled', false)->sum('amount');

        // Currency summary for top cards — exclude settled
        $currencyRows = (clone $q)
            ->where('is_settled', false)
            ->reorder()
            ->selectRaw('currency, type, SUM(amount) as total')
            ->groupBy('currency', 'type')
            ->get();
        $currencySummary = [];
        foreach ($currencyRows as $row) {
            $code = strtoupper($row->currency ?? 'TRY');
            if (!isset($currencySummary[$code])) {
                $currencySummary[$code] = ['income' => 0.0, 'expense' => 0.0, 'net' => 0.0];
            }

            if ($row->type === 'income') {
                $currencySummary[$code]['income'] = (float) $row->total;
            } else {
                $currencySummary[$code]['expense'] = (float) $row->total;
            }
            $currencySummary[$code]['net'] = $currencySummary[$code]['income'] - $currencySummary[$code]['expense'];
        }

        // Format transactions for JSON response
        $formattedTransactions = $transactions->map(function ($t) use ($selectedAgencyId) {
            $ticketLinkedMethods = ['auto-expired-ticket', 'sale-ticket', 'payout-owner', 'owner-share', 'rest-adjustment'];
            $isAuto = in_array($t->payment_method, ['auto-expired-ticket', 'salary-auto', 'sale-ticket', 'payout-owner', 'owner-share', 'rest-adjustment']);

            $displayTitle = $t->title;
            $ownerShareLabel = 'Acenta Satışı';
            
            $statusMap = ['paid' => 'success', 'pending' => 'warning', 'cancelled' => 'secondary'];
            $methodLabels = [
                'auto-expired-ticket' => 'Otomatik (Bilet)',
                'sale-ticket' => 'Bilet Satışı',
                'payout-owner' => 'Tur Sahibi Payı',
                'owner-share' => $ownerShareLabel,
                'salary-auto' => 'Maaş Ödemesi',
            ];

            return [
                'id' => $t->id,
                'date' => $t->transaction_date->format('d.m.Y'),
                'date_iso' => $t->transaction_date->format('Y-m-d'),
                'title' => $displayTitle,
                'type' => $t->type,
                'type_label' => $t->type === 'income' ? 'Gelir' : 'Gider',
                'type_badge' => $t->type === 'income' ? 'success' : 'danger',
                'amount' => number_format($t->amount, 2, ',', '.'),
                'amount_raw' => $t->amount,
                'currency' => $t->currency,
                'status' => $t->status,
                'status_badge' => $statusMap[$t->status] ?? 'secondary',
                'status_label' => ucfirst($t->status),
                'is_settled' => (bool) $t->is_settled,
                'settled_at' => $t->settled_at ? $t->settled_at->format('d.m.Y H:i') : null,
                'payment_method' => $t->payment_method,
                'payment_label' => $methodLabels[$t->payment_method] ?? $t->payment_method ?? '-',
                'is_auto' => $isAuto,
                'is_salary' => $t->payment_method === 'salary-auto',
                'ticket_tracking' => $t->ticket ? $t->ticket->tracking_no : null,
                'ticket_id' => $t->ticket ? $t->ticket->id : null,
                'edit_url' => !$isAuto ? route('admin.accounting.edit', [
                    'transaction' => $t,
                    'locked_agency_id' => $selectedAgencyId,
                ]) : null,
                'delete_url' => !$isAuto ? route('admin.accounting.destroy', $t) : null,
                'ticket_url' => ($isAuto && $t->ticket) ? route('admin.tickets.show', $t->ticket) : null,
            ];
        });

        return response()->json([
            'transactions' => $formattedTransactions,
            'total_count' => $transactions->count(),
            'total_income' => number_format($totalIncome, 2, ',', '.'),
            'total_expense' => number_format($totalExpense, 2, ',', '.'),
            'currency_summary' => collect($currencySummary)->map(function (array $summary) {
                return [
                    'income' => (float) ($summary['income'] ?? 0),
                    'expense' => (float) ($summary['expense'] ?? 0),
                    'net' => (float) ($summary['net'] ?? 0),
                    'income_formatted' => number_format((float) ($summary['income'] ?? 0), 2, ',', '.'),
                    'expense_formatted' => number_format((float) ($summary['expense'] ?? 0), 2, ',', '.'),
                    'net_formatted' => number_format((float) ($summary['net'] ?? 0), 2, ',', '.'),
                ];
            })->toArray(),
        ]);
    }

    public function submitSettlement(Request $request)
    {
        $data = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
        ]);

        $ticketIds = collect($data['ticket_ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();
        if (empty($ticketIds)) {
            return response()->json(['message' => 'Geçerli bilet seçimi bulunamadı.'], 422);
        }

        $tickets = Ticket::whereIn('id', $ticketIds)
            ->get([
                'id',
                'created_by_user_id',
                'accounting_transaction_id',
                'accounting_owner_transaction_id',
                'accounting_rest_transaction_id',
                'accounting_agency_income_transaction_id',
                'accounting_agency_payout_transaction_id',
            ]);

        if ($tickets->isEmpty()) {
            return response()->json(['message' => 'Seçili biletler bulunamadı.'], 422);
        }

        $agencyUsers = User::whereIn('id', $tickets->pluck('created_by_user_id')->filter()->unique()->values()->all())
            ->get(['id', 'level'])
            ->keyBy('id');

        $isAgencyTicket = function (Ticket $ticket) use ($agencyUsers): bool {
            $creatorId = (int) ($ticket->created_by_user_id ?? 0);

            return $creatorId > 0
                && isset($agencyUsers[$creatorId])
                && $agencyUsers[$creatorId]->isAgency();
        };

        $ownSaleTickets = $tickets->filter(fn (Ticket $t) => !$isAgencyTicket($t));
        $agencyTickets = $tickets->filter($isAgencyTicket);

        $immediateDeletedCount = 0;
        if ($ownSaleTickets->isNotEmpty()) {
            $ownTicketIds = $ownSaleTickets->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $immediateDeletedCount = (int) DB::transaction(
                fn () => SettlementRequest::applyTicketSettlementRemovals($ownTicketIds)
            );
        }

        $groupedTickets = $agencyTickets->groupBy('created_by_user_id');

        $createdRequests = collect();
        foreach ($groupedTickets as $agencyId => $group) {
            $validTicketIds = $group->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $validIds = $group->flatMap(function (Ticket $ticket) {
                return [
                    $ticket->accounting_transaction_id,
                    $ticket->accounting_owner_transaction_id,
                    $ticket->accounting_rest_transaction_id,
                    $ticket->accounting_agency_income_transaction_id,
                    $ticket->accounting_agency_payout_transaction_id,
                ];
            })->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

            if (count($validIds) === 0) {
                continue;
            }

            $createdRequests->push(SettlementRequest::create([
                'admin_user_id' => (int) auth()->id(),
                'agency_user_id' => (int) $agencyId,
                'ticket_ids' => array_values($validTicketIds),
                'transaction_ids' => array_values($validIds),
                'status' => SettlementRequest::STATUS_PENDING,
                'note' => 'Admin tarafından mutabakat onayına gönderildi.',
            ]));
        }

        if ($immediateDeletedCount === 0 && $createdRequests->isEmpty()) {
            return response()->json(['message' => 'Seçili biletler kapsamında işlenecek muhasebe kalemi bulunamadı.'], 422);
        }

        $immediateApplied = $immediateDeletedCount > 0;
        $messages = [];

        if ($immediateApplied) {
            $messages[] = 'Kendi satış biletler için mutabakat tamamlandı (kasadan düşüldü).';
        }

        if ($createdRequests->isNotEmpty()) {
            $messages[] = $createdRequests->count() > 1
                ? 'Mutabakat talepleri acentalara göre ayrı ayrı gönderildi.'
                : 'Mutabakat acentanın onayına gönderildi.';
        }

        return response()->json([
            'message' => implode(' ', $messages),
            'request_ids' => $createdRequests->pluck('id')->values()->all(),
            'request_count' => $createdRequests->count(),
            'immediate_settlement_applied' => $immediateApplied,
            'immediate_transactions_settled' => $immediateDeletedCount,
        ]);
    }

    public function create()
    {
        return view('admin.accounting.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'required|in:paid,pending,cancelled',
            'notes' => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        Transaction::create($data);

        $redirectParams = [];
        if ($request->filled('locked_agency_id')) {
            $redirectParams['locked_agency_id'] = (int) $request->input('locked_agency_id');
        }

        return redirect()->route('admin.accounting.index', $redirectParams)->with('success', 'Kayıt eklendi.');
    }

    protected function isLocked(Transaction $transaction): bool
    {
        return in_array($transaction->payment_method, [
            'auto-expired-ticket',
            'salary-auto',
            'sale-ticket',
            'payout-owner',
            'owner-share',
            'rest-adjustment',
        ], true);
    }

    public function edit(Transaction $transaction)
    {
        if ($this->isLocked($transaction)) {
            $redirectParams = [];
            if (request()->filled('locked_agency_id')) {
                $redirectParams['locked_agency_id'] = (int) request('locked_agency_id');
            }
            return redirect()
                ->route('admin.accounting.index', $redirectParams)
                ->with('error', 'Bu kayıt otomatik oluşturuldu ve düzenlenemez.');
        }
        return view('admin.accounting.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $redirectParams = [];
        if ($request->filled('locked_agency_id')) {
            $redirectParams['locked_agency_id'] = (int) $request->input('locked_agency_id');
        }

        if ($this->isLocked($transaction)) {
            return redirect()
                ->route('admin.accounting.index', $redirectParams)
                ->with('error', 'Bu kayıt otomatik oluşturuldu ve düzenlenemez.');
        }
        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'transaction_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'required|in:paid,pending,cancelled',
            'notes' => 'nullable|string',
        ]);
        $transaction->update($data);
        return redirect()->route('admin.accounting.index', $redirectParams)->with('success', 'Kayıt güncellendi.');
    }

    public function destroy(Transaction $transaction)
    {
        if ($this->isLocked($transaction)) {
            return redirect()
                ->route('admin.accounting.index')
                ->with('error', 'Bu kayıt otomatik oluşturuldu ve silinemez.');
        }
        $transaction->delete();
        return back()->with('success', 'Kayıt silindi.');
    }

    /**
     * Export filtered transactions to Excel
     */
    public function exportExcel(Request $request)
    {
        $q = $this->scopedQuery();
        $selectedAgencyId = $this->resolveAgencyFilterId($request);

        // Apply same filters as index
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
        if ($search = request('search')) {
            $q->whereMatchesAccountingSearch($search);
        }
        if ($from = request('from')) {
            $q->whereDate('transaction_date', '>=', $from);
        }
        if ($to = request('to')) {
            $q->whereDate('transaction_date', '<=', $to);
        }
        $this->applyAgencyFilter($q, $selectedAgencyId);

        $q->with('creator')->latest('transaction_date');

        $filename = 'muhasebe_kayitlari_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new TransactionsExport($q), $filename);
    }
}



