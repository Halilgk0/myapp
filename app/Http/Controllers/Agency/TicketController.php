<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketPassenger;
use App\Models\TicketRequest;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $perPage = (int) request('per_page', 10);
        if ($perPage <= 0) { $perPage = 10; }

        // Get only tickets created by this agency user
        $query = Ticket::with(['vehicle', 'passengers', 'tour'])
            ->where('created_by_user_id', $user->id);

        // Smart search across multiple fields
        if ($q = trim(request('q', ''))) {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($tokens as $token) {
                $like = '%' . $token . '%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('tracking_no', 'like', $like)
                        ->orWhere('voucher_no', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like)
                        ->orWhere('room_number', 'like', $like);
                });
            }
        }

        // Filters
        if ($tourId = request('filter_tour_id')) {
            $query->where('tour_id', $tourId);
        }
        if ($from = request('filter_from')) {
            $query->whereDate('tour_date', '>=', $from);
        }
        if ($to = request('filter_to')) {
            $query->whereDate('tour_date', '<=', $to);
        }
        if (request()->filled('filter_is_active')) {
            $query->where('is_active', request('filter_is_active') === '1');
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Get shared tours for filter dropdown
        $sharedTourIds = $user->sharedTours()->pluck('tours.id');
        $tours = Tour::whereIn('id', $sharedTourIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get pending ticket requests for this user
        $pendingRequests = TicketRequest::where('requester_id', $user->id)
            ->pending()
            ->with('tour')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get returned ticket requests for this user (need editing)
        $returnedRequests = TicketRequest::where('requester_id', $user->id)
            ->where('status', TicketRequest::STATUS_RETURNED)
            ->with('tour')
            ->orderBy('returned_at', 'desc')
            ->get();

        return view('agency.tickets.index', compact('tickets', 'perPage', 'tours', 'pendingRequests', 'returnedRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Get shared tours with custom pricing info and owner info
        $sharedTours = $user->sharedTours()
            ->where('is_active', true)
            ->with('owner')
            ->withCount('tickets')
            ->orderBy('name')
            ->get()
            ->map(function (Tour $tour) {
                $pivot = $tour->pivot;
                $customDatePrices = $pivot && $pivot->custom_date_prices
                    ? json_decode($pivot->custom_date_prices, true)
                    : null;
                $customCurrency = $pivot && $pivot->custom_currency
                    ? $pivot->custom_currency
                    : null;

                // If custom prices exist, use them; otherwise use tour's default
                $tour->agency_date_prices = $customDatePrices ?: $tour->date_prices;
                $tour->agency_currency = $customCurrency ?: $tour->currency;

                return $tour;
            });

        // Default date/time
        $defaultDate = \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d');
        $defaultTime = \Carbon\Carbon::now('Europe/Istanbul')->format('H:i');

        return view('agency.tickets.create', compact('sharedTours', 'defaultDate', 'defaultTime'));
    }

    /**
     * Store a newly created resource in storage.
     * Creates a TicketRequest that needs approval from tour owner.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'voucher_no' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('tickets', 'voucher_no')->whereNull('deleted_at')],
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'passport_numbers' => 'nullable|string',
            'customer_nationality' => 'required|string|max:10',
            'tour_id' => 'required|exists:tours,id',
            'tour_date' => 'required|date',
            'pickup_time' => 'nullable|date_format:H:i',
            'adult_count' => 'required|integer|min:0',
            'child_count' => 'required|integer|min:0',
            'infant_count' => 'required|integer|min:0',
            'pickup_location' => 'nullable|string|max:500',
            'room_number' => 'nullable|string|max:50',
            'sale_total_price' => 'nullable|numeric|min:0',
            'rest_amount' => 'nullable|numeric|min:0',
            'sale_currency' => 'nullable|string|in:TRY,USD,EUR,GBP,RUB',
            'rest_currency' => 'nullable|string|in:TRY,USD,EUR,GBP,RUB',
            'pickup_lat' => 'nullable|numeric|between:-90,90',
            'pickup_lng' => 'nullable|numeric|between:-180,180',
        ], [
            'voucher_no.unique' => 'Bu voucher numarası başka bir bilete ait — farklı bir numara giriniz.',
        ]);

        // Verify tour is shared with this user
        $sharedTourIds = $user->sharedTours()->pluck('tours.id')->toArray();
        if (!in_array($request->tour_id, $sharedTourIds)) {
            return back()->withErrors(['tour_id' => 'Bu tura erişim yetkiniz yok.']);
        }

        $tour = Tour::findOrFail($request->tour_id);

        // Get custom pricing if exists
        $pivot = DB::table('tour_shared_users')
            ->where('tour_id', $tour->id)
            ->where('shared_with_user_id', $user->id)
            ->first();

        $customDatePrices = $pivot && $pivot->custom_date_prices
            ? json_decode($pivot->custom_date_prices, true)
            : null;
        $customCurrency = $pivot && $pivot->custom_currency
            ? $pivot->custom_currency
            : null;

        // Selected date
        $selectedDate = \Carbon\Carbon::parse($request->tour_date);
        $dateKey = $selectedDate->format('Y-m-d');

        // Check if selected date is available in tour
        $availableDates = $tour->available_dates ?? [];
        if (!empty($availableDates) && !in_array($dateKey, $availableDates)) {
            return back()->withErrors(['tour_date' => 'Seçilen tarih bu tur için uygun değil.']);
        }

        // Tur tarihi (ve varsa saati) İstanbul saatine göre geçmişte olamaz.
        // pickup_time opsiyonel; boşsa günün sonu baz alınır.
        $timePart = $request->filled('pickup_time') ? $request->pickup_time : '23:59';
        $tourDateTime = \Carbon\Carbon::parse(
            $request->tour_date . ' ' . $timePart,
            'Europe/Istanbul'
        );
        if ($tourDateTime->lt(\Carbon\Carbon::now('Europe/Istanbul'))) {
            return back()->withErrors(['tour_date' => 'Geçmiş tarihe/saate bilet oluşturulamaz.']);
        }

        // Calculate prices - use custom prices if available
        $currency = $customCurrency ?: ($tour->currency ?? 'TRY'); // taban para birimi

        if ($customDatePrices && isset($customDatePrices[$dateKey])) {
            $prices = $customDatePrices[$dateKey];
            $adultPrice = (float) ($prices['adult'] ?? 0);
            $childPrice = (float) ($prices['child'] ?? 0);
            $infantPrice = (float) ($prices['infant'] ?? 0);
            // Günün kendi para birimi varsa onu kullan
            if (!empty($prices['currency'])) {
                $currency = $prices['currency'];
            }
        } else {
            // Fallback to tour's pricing
            $prices = $tour->getPricesForDate($selectedDate);
            $adultPrice = $prices['adult'] ?? 0;
            $childPrice = $prices['child'] ?? 0;
            $infantPrice = $prices['infant'] ?? 0;
        }

        $baseCurrency = strtoupper($currency ?? 'TRY'); // Taban para birimi değiştirilmez
        $saleCurrency = strtoupper($request->input('sale_currency', $baseCurrency));
        $restCurrency = strtoupper($request->input('rest_currency', $baseCurrency));

        $adultCount = $request->adult_count;
        $childCount = $request->child_count;
        $infantCount = $request->infant_count;

        $baseTotal = ($adultCount * $adultPrice) + ($childCount * $childPrice) + ($infantCount * $infantPrice);

        // Rest: adminin alacağı ek gelir, taban payından düşülür (kur ile)
        $restInput = $request->input('rest_amount');
        $restAmount = $restInput !== null ? (float) $restInput : 0;
        if ($restAmount < 0) {
            $restAmount = 0;
        }

        $restConverted = $restAmount;
        $restFxRate = 1.0;
        $restFxDate = now('Europe/Istanbul')->toDateString();

        if ($restAmount > 0 && $restCurrency !== $baseCurrency) {
            try {
                $rates = $this->getTcmbRatesMap();
                $conversion = $this->convertFxAmount($restAmount, $restCurrency, $baseCurrency, $rates);
                $restConverted = $conversion['converted'];
                $restFxRate = $conversion['rate'];
            } catch (\Throwable $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'rest_amount' => 'Kur alınamadı: ' . $e->getMessage(),
                ]);
            }
        }

        $ownerShareAmount = max($baseTotal - $restConverted, 0);

        // Sokak acentasının satacağı fiyat (taban altına izin verilebilir)
        $saleTotalInput = $request->input('sale_total_price');
        $saleTotal = $saleTotalInput !== null ? (float) $saleTotalInput : $baseTotal;

        // Check if auto approval should be applied
        $shouldAutoApprove = false;
        
        // Check if tour has auto_approve_tickets enabled
        if ($tour->auto_approve_tickets) {
            $shouldAutoApprove = true;
        }
        
        // Check if street agency auto approve time condition is met
        // DEBUG: Log all values
        \Log::info('Street Agency Auto Approve Check', [
            'user_id' => $user->id,
            'user_level' => $user->level,
            'isAgency' => $user->isAgency(),
            'tour_id' => $tour->id,
            'street_agency_auto_approve_time' => $tour->street_agency_auto_approve_time,
            'street_agency_auto_approve_time_raw' => $tour->getRawOriginal('street_agency_auto_approve_time'),
            'street_agency_auto_approve_enabled' => $tour->street_agency_auto_approve_enabled,
            'auto_approve_tickets' => $tour->auto_approve_tickets,
        ]);
        
        // Sokak acentası saat kontrolü - user level 3 ise agency
        $isStreetAgency = $user->level === 3 || $user->isAgency();
        $autoApproveTime = $tour->getRawOriginal('street_agency_auto_approve_time') ?? $tour->street_agency_auto_approve_time;
        
        if ($isStreetAgency && $autoApproveTime) {
            // Varsayılan: toggle değeri null ise açık kabul et
            $enabled = $tour->street_agency_auto_approve_enabled ?? true;

            \Log::info('Street Agency Time Check', [
                'enabled' => $enabled,
                'autoApproveTime' => $autoApproveTime,
            ]);

            if ($enabled) {
                // İstanbul saat dilimiyle kıyasla
                $nowTr = now('Europe/Istanbul');
                
                // Saat değerini string olarak al
                $timeStr = $autoApproveTime;
                if ($autoApproveTime instanceof \Carbon\Carbon) {
                    $timeStr = $autoApproveTime->format('H:i');
                } elseif (is_string($autoApproveTime) && strlen($autoApproveTime) > 5) {
                    // Eğer datetime formatında ise sadece saat kısmını al
                    $timeStr = substr($autoApproveTime, 0, 5);
                }
                
                $currentTimeStr = $nowTr->format('H:i');
                
                \Log::info('Time Comparison', [
                    'timeStr' => $timeStr,
                    'currentTimeStr' => $currentTimeStr,
                    'comparison' => $currentTimeStr >= $timeStr,
                ]);
                
                // String olarak karşılaştır (H:i formatı)
                if ($currentTimeStr >= $timeStr) {
                    $shouldAutoApprove = true;
                    \Log::info('Auto approve triggered for street agency');
                }
            }
        }

        if ($shouldAutoApprove) {
            // Create ticket directly without approval
            $ticket = Ticket::create([
                'voucher_no' => $request->voucher_no,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'customer_nationality' => $request->customer_nationality,
                'tour_id' => $tour->id,
                'tour_date' => $request->tour_date,
                'tour_name' => $tour->name,
                'tour_country' => $tour->country,
                'tour_region' => $tour->city,
                'pickup_time' => $request->pickup_time,
                'sales_agency' => $user->agency ? $user->agency->name : $user->name,
                'total_price' => $saleTotal,
                'deposit' => $saleTotal * 0.3,
                'rest' => $saleTotal * 0.7,
                'currency' => $saleCurrency,
                'base_currency' => $baseCurrency,
                'sale_currency' => $saleCurrency,
                'is_active' => true,
                'entry_date' => now()->format('Y-m-d'),
                'entry_time' => now()->format('H:i:s'),
                'created_by_user_id' => $user->id,
                'adult_count' => $adultCount,
                'child_count' => $childCount,
                'infant_count' => $infantCount,
                'adult_price' => $adultPrice,
                'child_price' => $childPrice,
                'infant_price' => $infantPrice,
                'owner_share_amount' => $ownerShareAmount,
                'owner_share_currency' => $baseCurrency,
                'rest_adjustment_amount' => $restAmount,
                'rest_adjustment_currency' => $restCurrency,
                'rest_converted_amount' => $restConverted,
                'rest_fx_rate' => $restFxRate,
                'rest_fx_source_currency' => $restCurrency,
                'rest_fx_target_currency' => $baseCurrency,
                'rest_fx_date' => $restFxDate,
                'pickup_location' => $request->pickup_location,
                'room_number' => $request->room_number,
            ]);

            // Create passenger records for pricing/summary
            if ($adultCount > 0) {
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_type' => 'adult',
                    'quantity' => $adultCount,
                    'unit_price' => $adultPrice,
                    'total_price' => $adultCount * $adultPrice,
                ]);
            }

            if ($childCount > 0) {
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_type' => 'child',
                    'quantity' => $childCount,
                    'unit_price' => $childPrice,
                    'total_price' => $childCount * $childPrice,
                ]);
            }

            if ($infantCount > 0) {
                TicketPassenger::create([
                    'ticket_id' => $ticket->id,
                    'passenger_type' => 'infant',
                    'quantity' => $infantCount,
                    'unit_price' => $infantPrice,
                    'total_price' => $infantCount * $infantPrice,
                ]);
            }

            if ($request->filled('pickup_lat') && $request->filled('pickup_lng')) {
                \App\Models\TicketLocation::updateOrCreate(
                    ['ticket_id' => $ticket->id],
                    ['latitude' => (float)$request->pickup_lat, 'longitude' => (float)$request->pickup_lng, 'accuracy' => null]
                );
            }

            $successMessage = 'Bilet başarıyla oluşturuldu (otomatik onay).';
            if ($user->isAgency() && $tour->street_agency_auto_approve_time) {
                $successMessage .= ' Sokak acentası otomatik onay saatine göre onaylandı.';
            }
            
            return redirect()->route('agency.tickets.index')
                ->with('success', $successMessage);
        }

        // Create a ticket request (needs approval from tour owner)
        TicketRequest::create([
            'requester_id' => $user->id,
            'tour_owner_id' => $tour->owner_id,
            'tour_id' => $tour->id,
            'voucher_no' => $request->voucher_no,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_nationality' => $request->customer_nationality,
            'tour_date' => $request->tour_date,
            'pickup_time' => $request->pickup_time,
            'adult_count' => $adultCount,
            'child_count' => $childCount,
            'infant_count' => $infantCount,
            'adult_price' => $adultPrice,
            'child_price' => $childPrice,
            'infant_price' => $infantPrice,
            'total_price' => $saleTotal,
            'currency' => $saleCurrency,
            'base_currency' => $baseCurrency,
            'sale_currency' => $saleCurrency,
            'rest_adjustment_amount' => $restAmount,
            'rest_adjustment_currency' => $restCurrency,
            'rest_converted_amount' => $restConverted,
            'rest_fx_rate' => $restFxRate,
            'rest_fx_source_currency' => $restCurrency,
            'rest_fx_target_currency' => $baseCurrency,
            'rest_fx_date' => $restFxDate,
            'pickup_location' => $request->pickup_location,
            'room_number' => $request->room_number,
            'passport_numbers' => $request->passport_numbers,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'status' => TicketRequest::STATUS_PENDING,
        ]);

        return redirect()->route('agency.tickets.index')
            ->with('success', 'Bilet isteği oluşturuldu. Tur sahibinin onayı bekleniyor.');
    }

    /**
     * Rest para birimi için canlı kur önizlemesi (TCMB).
     */
    public function fxPreview(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from' => 'required|string|in:TRY,USD,EUR,GBP,RUB',
            'to' => 'required|string|in:TRY,USD,EUR,GBP,RUB',
        ]);

        $amount = (float) $request->amount;
        $from = strtoupper($request->from);
        $to = strtoupper($request->to);

        try {
            $rates = $this->getTcmbRatesMap();
            $conversion = $this->convertFxAmount($amount, $from, $to, $rates);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Kur alınamadı: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'converted' => $conversion['converted'],
            'rate' => $conversion['rate'],
            'from' => $from,
            'to' => $to,
            'date' => now('Europe/Istanbul')->toDateString(),
        ]);
    }

    /**
     * TCMB kurları: TRY per unit (ForexBuying öncelikli).
     */
    protected function getTcmbRatesMap(): array
    {
        try {
            return \Cache::remember('tcmb_rates_map_4', 300, function () {
                return $this->fetchTcmbRatesMap();
            });
        } catch (\Throwable $e) {
            \Log::warning('Agency TCMB map cache write/read failed, using uncached flow: ' . $e->getMessage());
            return $this->fetchTcmbRatesMap();
        }
    }

    protected function fetchTcmbRatesMap(): array
    {
            $http = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders([
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'Accept' => 'application/xml',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ]);

            $resp = $http->get('https://www.tcmb.gov.tr/kurlar/today.xml', ['_ts' => time()]);
            if (!$resp->ok()) {
                throw new \RuntimeException('TCMB yanıt vermedi');
            }

            $xml = @simplexml_load_string($resp->body());
            if (!$xml) {
                throw new \RuntimeException('TCMB verisi çözülemedi');
            }

            $wanted = ['USD', 'EUR', 'GBP', 'RUB'];
            $map = ['TRY' => 1.0];

            foreach ($xml->Currency as $cur) {
                $code = (string) $cur['CurrencyCode'];
                if (!in_array($code, $wanted, true)) {
                    continue;
                }
                $buyStr = (string) $cur->ForexBuying;
                if ($buyStr === '') {
                    $buyStr = (string) $cur->BanknoteBuying;
                }
                $rate = (float) str_replace(',', '.', $buyStr);
                if ($rate > 0) {
                    $map[$code] = $rate;
                }
            }

            if (count($map) <= 1) {
                throw new \RuntimeException('Kur bulunamadı');
            }

            return $map;
        
    }

    /**
     * amount * (TRY per from) / (TRY per to)
     * Returns converted + applied rate (target per source).
     */
    protected function convertFxAmount(float $amount, string $from, string $to, array $rates): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if ($from === $to) {
            return ['converted' => $amount, 'rate' => 1.0];
        }

        $fromRate = $from === 'TRY' ? 1.0 : ($rates[$from] ?? null);
        $toRate = $to === 'TRY' ? 1.0 : ($rates[$to] ?? null);

        if (!$fromRate || !$toRate) {
            throw new \RuntimeException("Kur eksik: {$from} veya {$to}");
        }

        $factor = $fromRate / $toRate; // target per source
        return [
            'converted' => $amount * $factor,
            'rate' => $factor,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        // Only allow viewing own tickets
        if ($ticket->created_by_user_id !== $user->id) {
            abort(403, 'Bu bileti görüntüleme yetkiniz yok.');
        }

        $ticket->load(['vehicle', 'passengers', 'tour']);

        return view('agency.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        $user = Auth::user();

        // Only allow editing own tickets
        if ($ticket->created_by_user_id !== $user->id) {
            abort(403, 'Bu bileti düzenleme yetkiniz yok.');
        }

        // Get shared tours with custom pricing info
        $sharedTours = $user->sharedTours()
            ->where('is_active', true)
            ->with('owner')
            ->orderBy('name')
            ->get()
            ->map(function (Tour $tour) {
                $pivot = $tour->pivot;
                $customDatePrices = $pivot && $pivot->custom_date_prices
                    ? json_decode($pivot->custom_date_prices, true)
                    : null;
                $customCurrency = $pivot && $pivot->custom_currency
                    ? $pivot->custom_currency
                    : null;

                $tour->agency_date_prices = $customDatePrices ?: $tour->date_prices;
                $tour->agency_currency = $customCurrency ?: $tour->currency;

                return $tour;
            });

        $ticket->load(['passengers', 'location']);

        return view('agency.tickets.edit', compact('ticket', 'sharedTours'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        // Only allow updating own tickets
        if ($ticket->created_by_user_id !== $user->id) {
            abort(403, 'Bu bileti düzenleme yetkiniz yok.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_nationality' => 'required|string|max:10',
            'pickup_location' => 'nullable|string|max:500',
            'room_number' => 'nullable|string|max:50',
            'passport_numbers' => 'nullable|string',
            'pickup_lat' => 'nullable|numeric|between:-90,90',
            'pickup_lng' => 'nullable|numeric|between:-180,180',
            'pickup_time' => 'nullable|date_format:H:i',
        ]);

        $updateData = [
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_nationality' => $request->customer_nationality,
            'pickup_location' => $request->pickup_location,
            'room_number' => $request->room_number,
            'passport_numbers' => $request->passport_numbers,
        ];
        if ($request->has('pickup_time')) {
            $updateData['pickup_time'] = $request->pickup_time ?: null;
        }
        $ticket->update($updateData);

        if ($request->filled('pickup_lat') && $request->filled('pickup_lng')) {
            \App\Models\TicketLocation::updateOrCreate(
                ['ticket_id' => $ticket->id],
                ['latitude' => (float)$request->pickup_lat, 'longitude' => (float)$request->pickup_lng, 'accuracy' => null]
            );
        }

        return redirect()->route('agency.tickets.index')
            ->with('success', 'Bilet başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $user = Auth::user();

        // Only allow deleting own tickets
        if ($ticket->created_by_user_id !== $user->id) {
            abort(403, 'Bu bileti silme yetkiniz yok.');
        }

        // Delete passengers first
        $ticket->passengers()->delete();
        $ticket->delete();

        return redirect()->route('agency.tickets.index')
            ->with('success', 'Bilet başarıyla silindi.');
    }

    /**
     * Cancel a pending ticket request.
     */
    public function cancelRequest(TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        if ($ticketRequest->requester_id !== $user->id) {
            abort(403, 'Bu isteği iptal etme yetkiniz yok.');
        }

        if (!$ticketRequest->isPending()) {
            return back()->with('error', 'Bu istek zaten işlenmiş.');
        }

        $ticketRequest->delete();

        return back()->with('success', 'Bilet isteği iptal edildi.');
    }

    /**
     * Get tour details for AJAX request
     */
    public function getTourDetails(Tour $tour)
    {
        $user = Auth::user();

        // Verify tour is shared with this user
        $sharedTourIds = $user->sharedTours()->pluck('tours.id')->toArray();
        if (!in_array($tour->id, $sharedTourIds)) {
            return response()->json(['error' => 'Bu tura erişim yetkiniz yok.'], 403);
        }

        // Get custom pricing if exists
        $pivot = DB::table('tour_shared_users')
            ->where('tour_id', $tour->id)
            ->where('shared_with_user_id', $user->id)
            ->first();

        $customDatePrices = $pivot && $pivot->custom_date_prices
            ? json_decode($pivot->custom_date_prices, true)
            : null;
        $customCurrency = $pivot && $pivot->custom_currency
            ? $pivot->custom_currency
            : null;

        return response()->json([
            'success' => true,
            'tour' => [
                'id' => $tour->id,
                'name' => $tour->name,
                'country' => $tour->country,
                'city' => $tour->city,
                'pickup_time' => $tour->pickup_time,
                'currency' => $customCurrency ?: $tour->currency,
                'available_dates' => $tour->available_dates ?? [],
                'date_prices' => $customDatePrices ?: ($tour->date_prices ?? []),
                'service_areas' => $tour->service_areas,
            ],
        ]);
    }

    /**
     * Show the form for editing a returned ticket request.
     */
    public function editRequest(TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        // Verify this request belongs to this user
        if ($ticketRequest->requester_id !== $user->id) {
            abort(403, 'Bu isteği düzenleme yetkiniz yok.');
        }

        // Only returned requests can be edited by agency
        if (!$ticketRequest->isReturned()) {
            return redirect()->route('agency.tickets.index')
                ->with('error', 'Sadece düzenleme için geri gönderilen istekler düzenlenebilir.');
        }

        $ticketRequest->load('tour');

        // Get shared tours for this user
        $sharedTourIds = $user->sharedTours()->pluck('tours.id')->toArray();
        $tours = Tour::whereIn('id', $sharedTourIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'country', 'city']);

        return view('agency.tickets.edit-request', compact('ticketRequest', 'tours'));
    }

    /**
     * Update the returned ticket request and resubmit.
     */
    public function updateRequest(Request $request, TicketRequest $ticketRequest)
    {
        $user = Auth::user();

        // Verify this request belongs to this user
        if ($ticketRequest->requester_id !== $user->id) {
            abort(403, 'Bu isteği güncelleme yetkiniz yok.');
        }

        // Only returned requests can be edited by agency
        if (!$ticketRequest->isReturned()) {
            return redirect()->route('agency.tickets.index')
                ->with('error', 'Sadece düzenleme için geri gönderilen istekler düzenlenebilir.');
        }

        $validated = $request->validate([
            'voucher_no' => 'nullable|string|max:100',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_nationality' => 'required|string|max:5',
            'tour_date' => 'required|date',
            'adult_count' => 'required|integer|min:0',
            'child_count' => 'required|integer|min:0',
            'infant_count' => 'required|integer|min:0',
            'pickup_location' => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:50',
        ]);

        $tour = Tour::findOrFail($ticketRequest->tour_id);
        $sharedTourIds = $user->sharedTours()->pluck('tours.id')->toArray();
        if (!in_array($tour->id, $sharedTourIds, true)) {
            return back()->withErrors(['tour_date' => 'Bu tura erişim yetkiniz yok.'])->withInput();
        }

        $selectedDate = \Carbon\Carbon::parse($validated['tour_date']);
        $dateKey = $selectedDate->format('Y-m-d');

        $availableDates = $tour->available_dates ?? [];
        if (!empty($availableDates) && !in_array($dateKey, $availableDates, true)) {
            return back()->withErrors(['tour_date' => 'Seçilen tarih bu tur için uygun değil.'])->withInput();
        }

        if ($selectedDate->isPast()) {
            return back()->withErrors(['tour_date' => 'Geçmiş bir tarih seçemezsiniz.'])->withInput();
        }

        // Recalculate unit prices from tour calendar/custom shared pricing.
        $pivot = DB::table('tour_shared_users')
            ->where('tour_id', $tour->id)
            ->where('shared_with_user_id', $user->id)
            ->first();

        $customDatePrices = $pivot && $pivot->custom_date_prices
            ? json_decode($pivot->custom_date_prices, true)
            : null;
        $customCurrency = $pivot && $pivot->custom_currency
            ? $pivot->custom_currency
            : null;

        $currency = strtoupper($customCurrency ?: ($tour->currency ?? 'TRY'));
        if ($customDatePrices && isset($customDatePrices[$dateKey])) {
            $prices = $customDatePrices[$dateKey];
            $adultPrice = (float) ($prices['adult'] ?? 0);
            $childPrice = (float) ($prices['child'] ?? 0);
            $infantPrice = (float) ($prices['infant'] ?? 0);
            if (!empty($prices['currency'])) {
                $currency = strtoupper($prices['currency']);
            }
        } else {
            $prices = $tour->getPricesForDate($selectedDate);
            $adultPrice = (float) ($prices['adult'] ?? 0);
            $childPrice = (float) ($prices['child'] ?? 0);
            $infantPrice = (float) ($prices['infant'] ?? 0);
        }

        $baseTotal = ((int) $validated['adult_count'] * $adultPrice)
            + ((int) $validated['child_count'] * $childPrice)
            + ((int) $validated['infant_count'] * $infantPrice);

        // Keep agency sale logic: total_price is sale price, not base price.
        // Recalculate sale total by preserving previous sale/base ratio.
        $oldBaseTotal = ((int) ($ticketRequest->adult_count ?? 0) * (float) ($ticketRequest->adult_price ?? 0))
            + ((int) ($ticketRequest->child_count ?? 0) * (float) ($ticketRequest->child_price ?? 0))
            + ((int) ($ticketRequest->infant_count ?? 0) * (float) ($ticketRequest->infant_price ?? 0));
        $oldSaleTotal = (float) ($ticketRequest->total_price ?? 0);
        $saleFactor = ($oldBaseTotal > 0 && $oldSaleTotal > 0) ? ($oldSaleTotal / $oldBaseTotal) : 1.0;
        $saleTotal = round($baseTotal * $saleFactor, 2);

        $validated['adult_price'] = $adultPrice;
        $validated['child_price'] = $childPrice;
        $validated['infant_price'] = $infantPrice;
        $validated['currency'] = strtoupper($ticketRequest->sale_currency ?: ($ticketRequest->currency ?: $currency));
        $validated['base_currency'] = $currency;
        $validated['sale_currency'] = strtoupper($ticketRequest->sale_currency ?: ($ticketRequest->currency ?: $currency));
        $validated['total_price'] = $saleTotal;
        
        // Change status back to pending (resubmit)
        $validated['status'] = TicketRequest::STATUS_PENDING;
        $validated['return_reason'] = null; // Clear the return reason

        $ticketRequest->update($validated);

        return redirect()->route('agency.tickets.index')
            ->with('success', 'Bilet isteği güncellendi ve tekrar gönderildi. Onay bekleniyor.');
    }
}
