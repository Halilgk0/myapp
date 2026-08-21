<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesAgencyVisibility;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
    use HandlesAgencyVisibility;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = (int) request('per_page', 10);
        if ($perPage <= 0) { $perPage = 10; }

        $query = Tour::withCount([
            'tickets as total_tickets',
            'tickets as active_tickets' => function($query) {
                $query->where('is_active', true);
            }
        ])->where('owner_id', auth()->id());

        // Quick search by name only
        if ($q = trim(request('q', ''))) {
            $query->where('name', 'like', "%{$q}%");
        }

        $tours = $query
            ->orderBy('created_at', 'asc') // new tours bottom as requested
            ->paginate($perPage)
            ->appends(request()->query());

        return view('admin.tours.index', compact('tours', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tours.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'duration_days' => 'nullable|integer|min:0',
            'duration_hours' => 'nullable|integer|min:0',
            'pickup_time' => 'nullable',
            'dropoff_time' => 'nullable',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'price_adult' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'price_infant' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'max_capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            // image removed
            'available_days' => 'nullable|array',
            'available_months' => 'nullable|array',
            'available_years' => 'nullable|array',
            'selected_dates' => 'nullable|string',
            'available_dates' => 'nullable|array',
            'available_days.*' => 'integer|between:1,7',
            'available_months.*' => 'integer|between:1,12',
            'available_years.*' => 'integer|min:2020',
            'monthly_prices' => 'nullable|array',
            'monthly_prices.*.adult' => 'nullable|numeric|min:0',
            'monthly_prices.*.child' => 'nullable|numeric|min:0',
            'monthly_prices.*.infant' => 'nullable|numeric|min:0',
            'selected_prices' => 'nullable|string',
            'service_areas' => 'nullable|string',
            'auto_share_on_connect' => 'boolean',
            'auto_approve_tickets' => 'boolean',
            'street_agency_auto_approve_time' => 'nullable|date_format:H:i',
            'street_agency_auto_approve_enabled' => 'boolean',
        ]);

        try {
            // image handling removed

            $validated['is_active'] = $request->has('is_active');
            $validated['auto_share_on_connect'] = $request->boolean('auto_share_on_connect');
            $validated['auto_approve_tickets'] = $request->boolean('auto_approve_tickets');
            $validated['street_agency_auto_approve_enabled'] = $request->boolean('street_agency_auto_approve_enabled');
            // Eski sistem fiyatlarını saklama
            $validated['price_adult'] = 0;
            $validated['price_child'] = 0;
            $validated['price_infant'] = 0;
            $validated['monthly_prices'] = null;

            // Handle selected dates
            if ($request->has('selected_dates') && !empty($request->selected_dates)) {
                try {
                    $selectedDates = json_decode($request->selected_dates, true);
                    if (is_array($selectedDates)) {
                        $validated['available_dates'] = $selectedDates;
                    }
                } catch (\Exception $e) {
                    // If JSON decode fails, ignore selected_dates
                    unset($validated['selected_dates']);
                }
            }

            // Handle selected per-day prices
            if ($request->has('selected_prices') && !empty($request->selected_prices)) {
                try {
                    $selPrices = json_decode($request->selected_prices, true);
                    if (is_array($selPrices)) {
                        $validated['date_prices'] = $this->sanitizeDatePrices($selPrices);
                    }
                } catch (\Exception $e) {
                    unset($validated['selected_prices']);
                }
            }

            // Handle service areas (GeoJSON string) if provided
            if ($request->filled('service_areas')) {
                try {
                    $geo = json_decode($request->service_areas, true);
                    if (is_array($geo)) {
                        $validated['service_areas'] = $geo;
                    }
                } catch (\Throwable $e) {
                    // ignore invalid json
                }
            }

            // Remove non-column temp fields
            unset($validated['selected_dates']);
            unset($validated['selected_prices']);

            // Eski aylık fiyatlar artık kullanılmıyor.

            $validated['owner_id'] = auth()->id();

            $tour = Tour::create($validated);

            // Share with all connected users if auto_share is enabled
            $this->shareWithConnectedUsers($tour);

            return redirect()->route('admin.tours.index')
                ->with('success', 'Tur başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            Log::error('Tour creation error: ' . $e->getMessage() . ' | Stack: ' . $e->getTraceAsString());
            
            $errorMessage = 'Tur oluşturulurken bir hata oluştu: ' . $e->getMessage();
            
            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tour $tour)
    {
        $this->authorizeTour($tour);

        $tour->load(['tickets' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.tours.show', compact('tour'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tour $tour)
    {
        $this->authorizeTour($tour);

        return view('admin.tours.edit', compact('tour'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tour $tour)
    {
        $this->authorizeTour($tour);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'duration_days' => 'nullable|integer|min:0',
            'duration_hours' => 'nullable|integer|min:0',
            'pickup_time' => 'nullable',
            'dropoff_time' => 'nullable',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'price_adult' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'price_infant' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'max_capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            // image removed
            'available_days' => 'nullable|array',
            'available_months' => 'nullable|array',
            'available_years' => 'nullable|array',
            'available_days.*' => 'integer|between:1,7',
            'available_months.*' => 'integer|between:1,12',
            'available_years.*' => 'integer|min:2020',
            'monthly_prices' => 'nullable|array',
            'monthly_prices.*.adult' => 'nullable|numeric|min:0',
            'monthly_prices.*.child' => 'nullable|numeric|min:0',
            'monthly_prices.*.infant' => 'nullable|numeric|min:0',
            'selected_prices' => 'nullable|string',
            'selected_dates' => 'nullable|string',
            'service_areas' => 'nullable|string',
            'auto_share_on_connect' => 'boolean',
            'auto_approve_tickets' => 'boolean',
            'street_agency_auto_approve_time' => 'nullable|date_format:H:i',
            'street_agency_auto_approve_enabled' => 'boolean',
        ]);

        try {
            // image handling removed

            $validated['is_active'] = $request->has('is_active');
            $validated['auto_share_on_connect'] = $request->boolean('auto_share_on_connect');
            $validated['auto_approve_tickets'] = $request->boolean('auto_approve_tickets');
            $validated['street_agency_auto_approve_enabled'] = $request->boolean('street_agency_auto_approve_enabled');

            // Eski aylık fiyatlar artık kullanılmıyor.

            // Handle selected per-day prices
            if ($request->has('selected_prices') && !empty($request->selected_prices)) {
                try {
                    $selPrices = json_decode($request->selected_prices, true);
                    if (is_array($selPrices)) {
                        $validated['date_prices'] = $this->sanitizeDatePrices($selPrices);
                    }
                } catch (\Exception $e) {
                    unset($validated['selected_prices']);
                }
            }

            // Handle selected dates for availability
            if ($request->has('selected_dates') && !empty($request->selected_dates)) {
                try {
                    $selDates = json_decode($request->selected_dates, true);
                    if (is_array($selDates)) {
                        $validated['available_dates'] = $selDates;
                    }
                } catch (\Exception $e) {
                    unset($validated['selected_dates']);
                }
            }

            // Handle service areas (GeoJSON string)
            if ($request->filled('service_areas')) {
                try {
                    $geo = json_decode($request->service_areas, true);
                    if (is_array($geo)) {
                        $validated['service_areas'] = $geo;
                    } else {
                        $validated['service_areas'] = null;
                    }
                } catch (\Throwable $e) {
                    $validated['service_areas'] = null;
                }
            } else {
                $validated['service_areas'] = null;
            }

            unset($validated['selected_prices']);
            unset($validated['selected_dates']);

            // Eski sistem fiyatlarını saklama
            $validated['price_adult'] = 0;
            $validated['price_child'] = 0;
            $validated['price_infant'] = 0;
            $validated['monthly_prices'] = null;

            unset($validated['owner_id']);

            $tour->update($validated);

            // Share with all connected users if auto_share is enabled
            $this->shareWithConnectedUsers($tour);

            return redirect()->route('admin.tours.index')
                ->with('success', 'Tur başarıyla güncellendi.');

        } catch (\Exception $e) {
            Log::error('Tour update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tur güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tour $tour)
    {
        $this->authorizeTour($tour);

        try {
            // image deletion removed

            $tour->delete();

            return redirect()->route('admin.tours.index')
                ->with('success', 'Tur başarıyla silindi.');

        } catch (\Exception $e) {
            Log::error('Tour deletion error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Tur silinirken bir hata oluştu.');
        }
    }

    /**
     * Get available dates for a tour
     */
    public function getAvailableDates(Tour $tour)
    {
        $this->authorizeTour($tour);

        $availableDates = [];
        
        // Bugünden itibaren 6 ay boyunca uygun tarihleri hesapla
        $startDate = now()->startOfDay();
        $endDate = now()->addMonths(6)->endOfDay();
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0=Pazar, 1=Pazartesi, ...
            
            // Turun uygun günlerini kontrol et
            $availableDays = $tour->available_days ?? [];
            
            // Eğer available_days boşsa tüm günler uygun, değilse sadece belirtilen günler
            if (empty($availableDays) || in_array($dayOfWeek, $availableDays)) {
                $availableDates[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day' => $currentDate->format('d'),
                    'month' => $currentDate->format('M'),
                    'year' => $currentDate->format('Y'),
                    'dayOfWeek' => $dayOfWeek,
                    'formatted' => $currentDate->format('d M Y'),
                    'isToday' => $currentDate->isToday(),
                    'isPast' => $currentDate->isPast()
                ];
            }
            
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'available_dates' => $availableDates,
            'tour_info' => [
                'name' => $tour->name,
                'country' => $tour->country,
                'city' => $tour->city,
                'pickup_time' => $tour->pickup_time,
                'currency' => $tour->currency ?? 'TRY',
                'available_days' => $tour->available_days,
                'available_months' => $tour->available_months,
                'available_years' => $tour->available_years
            ]
        ]);
    }

    /**
     * Get tour details for ticket creation
     */
    public function getDetails(Tour $tour)
    {
        return response()->json([
            'success' => true,
            'tour' => [
                'id' => $tour->id,
                'name' => $tour->name,
                'country' => $tour->country,
                'city' => $tour->city,
                'pickup_time' => $tour->pickup_time,
                'currency' => $tour->currency ?? 'TRY',
                'available_days' => $tour->available_days ?? [],
                'available_months' => $tour->available_months ?? [],
                'available_years' => $tour->available_years ?? [],
                'available_dates' => $tour->available_dates ?? [],
                'date_prices' => $tour->date_prices ?? new \stdClass(),
                'service_areas' => $tour->service_areas ?? null,
            ]
        ]);
    }

    /**
     * Suggest distinct values for a given Tour field (e.g., country, region).
     */
    public function suggestions(Request $request)
    {
        $field = $request->get('field');
        $q = (string) $request->get('q', '');

        $allowed = ['country', 'city', 'district', 'pickup_location', 'dropoff_location'];
        if (!in_array($field, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz alan'
            ], 422);
        }

        $query = Tour::query()
            ->select($field)
            ->where('owner_id', auth()->id())
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->when(strlen($q) > 0, function ($builder) use ($field, $q) {
                $builder->where($field, 'like', '%' . $q . '%');
            })
            ->distinct()
            ->orderBy($field)
            ->limit(15)
            ->pluck($field);

        return response()->json([
            'success' => true,
            'field' => $field,
            'items' => $query
        ]);
    }

    /**
     * Export tours to styled XLS (HTML-based)
     */
    public function exportExcel(Request $request)
    {
        $query = Tour::withCount(['tickets as total_tickets','tickets as active_tickets' => function($q){ $q->where('is_active', true);}]);
        if ($q = trim($request->get('q',''))) { $query->where('name','like',"%{$q}%"); }
        $tours = $query->orderBy('created_at','asc')->get();

        $filename = 'tours_' . now()->format('Ymd_His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $html = '<html><head><meta charset="UTF-8">'
            .'<style>table{border-collapse:collapse;width:100%;font-family:Calibri,Arial,sans-serif;font-size:12px} th,td{border:1px solid #d0d7de;padding:6px} thead th{background:#2C3E50;color:#fff;font-weight:700;text-align:center;height:28px} tbody tr:nth-child(even){background:#ECF0F1} .num{text-align:right} .center{text-align:center}</style>'
            .'</head><body><table><colgroup>'
            .'<col style="width:60px"/><col style="width:240px"/><col style="width:130px"/><col style="width:130px"/><col style="width:130px"/><col style="width:110px"/><col style="width:110px"/><col style="width:80px"/><col style="width:80px"/><col style="width:80px"/><col style="width:90px"/>'
            .'</colgroup><thead><tr>'
            .'<th>ID</th><th>Ad</th><th>Ülke</th><th>Şehir</th><th>İlçe</th><th>Alış Saati</th><th>Fiyat (Max)</th><th>PB</th><th>Kapasite</th><th>Aktif</th><th>Toplam Bilet</th>'
            .'</tr></thead><tbody>';
        foreach ($tours as $t) {
            $maxPrice = (float) ($t->max_display_price ?? 0);
            $currencyLabel = $t->display_currency ?? ($t->currency ?? 'TRY');
            $html .= '<tr>'
                .'<td class="center">'.e($t->id).'</td>'
                .'<td>'.e($t->name).'</td>'
                .'<td>'.e($t->country).'</td>'
                .'<td>'.e($t->city).'</td>'
                .'<td>'.e($t->district).'</td>'
                .'<td class="center">'.e($t->earliest_service_area_time ?? optional($t->pickup_time)->format('H:i') ?? '-').'</td>'
                .'<td class="num">'.($maxPrice > 0 ? e(number_format($maxPrice,2,'.','')) : '-').'</td>'
                .'<td class="center">'.e($currencyLabel).'</td>'
                .'<td class="center">'.e($t->max_capacity).'</td>'
                .'<td class="center">'.($t->is_active ? 'Aktif' : 'Pasif').'</td>'
                .'<td class="center">'.e($t->total_tickets).'</td>'
                .'</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return response("\xEF\xBB\xBF".$html, 200, $headers);
    }

    /**
     * Export tours to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Tour::withCount(['tickets as total_tickets','tickets as active_tickets' => function($q){ $q->where('is_active', true);}]);
        if ($q = trim($request->get('q',''))) { $query->where('name','like',"%{$q}%"); }
        $tours = $query->orderBy('created_at','asc')->get();
        $html = view('admin.tours.pdf', compact('tours'))->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $filename = 'tours_'.now()->format('Ymd_His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    protected function authorizeTour(Tour $tour): void
    {
        if ($tour->owner_id !== auth()->id()) {
            abort(403, 'Bu tura erişim yetkiniz yok.');
        }
    }

    /**
     * Normalize per-day price payload: empty values become 0; ensure numeric floats.
     */
    private function sanitizeDatePrices(array $prices): array
    {
        $clean = [];
        foreach ($prices as $date => $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean[$date] = [
                'adult' => isset($row['adult']) && $row['adult'] !== '' ? (float) $row['adult'] : 0,
                'child' => isset($row['child']) && $row['child'] !== '' ? (float) $row['child'] : 0,
                'infant' => isset($row['infant']) && $row['infant'] !== '' ? (float) $row['infant'] : 0,
                'currency' => isset($row['currency']) && $row['currency'] !== '' ? strtoupper($row['currency']) : null,
            ];
        }

        return $clean;
    }

    /**
     * Share tour with all connected users when auto_share_on_connect is enabled.
     */
    private function shareWithConnectedUsers(Tour $tour): void
    {
        if (!$tour->auto_share_on_connect) {
            return;
        }

        $ownerId = $tour->owner_id;
        $connectedUserIds = $this->getConnectedUserIds($ownerId, false);

        if (empty($connectedUserIds)) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($connectedUserIds as $userId) {
            $rows[] = [
                'tour_id' => $tour->id,
                'shared_by_user_id' => $ownerId,
                'shared_with_user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('tour_shared_users')->upsert(
            $rows,
            ['tour_id', 'shared_with_user_id'],
            ['updated_at']
        );
    }
} 