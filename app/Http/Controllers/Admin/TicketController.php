<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketPassenger;
use App\Models\TicketRequest;
use App\Models\Vehicle;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\TicketsExport;
use Dompdf\Dompdf;

class TicketController extends Controller
{
    /**
     * Voucher No için anlık unique kontrolü (AJAX).
     * GET /admin/tickets/check-voucher?voucher_no=...&ignore_id=...
     */
    public function checkVoucher(Request $request)
    {
        $value = trim((string) $request->query('voucher_no', ''));
        $ignoreId = (int) $request->query('ignore_id', 0);

        if ($value === '') {
            return response()->json(['available' => true, 'empty' => true]);
        }

        $query = Ticket::withTrashed()->whereNull('deleted_at')->where('voucher_no', $value);
        if ($ignoreId > 0) {
            $query->where('id', '!=', $ignoreId);
        }
        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'Bu voucher numarası başka bir bilette kullanılmış.' : 'Voucher numarası uygun.',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = (int) request('per_page', 10);
        if ($perPage <= 0) { $perPage = 10; }

        $query = Ticket::with(['vehicle', 'passengers', 'tour']);

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
        // Country filter (supports legacy multi and new single)
        $filterCountries = request('filter_countries');
        $filterCountry = request('filter_country');
        if (is_array($filterCountries) && count($filterCountries) > 0) {
            $query->whereIn('tour_country', $filterCountries);
        } elseif (!empty($filterCountry)) {
            $query->where('tour_country', $filterCountry);
        }
        // City filter (supports legacy multi and new single)
        $filterCities = request('filter_cities');
        $filterCity = request('filter_city');
        if (is_array($filterCities) && count($filterCities) > 0) {
            $query->whereIn('tour_region', $filterCities);
        } elseif (!empty($filterCity)) {
            $query->where('tour_region', $filterCity);
        }
        if (request()->filled('filter_is_active')) {
            $query->where('is_active', request('filter_is_active') === '1');
        }
        if (request()->filled('filter_has_vehicle')) {
            if (request('filter_has_vehicle') === '1') {
                $query->whereNotNull('vehicle_id');
            } else {
                $query->whereNull('vehicle_id');
            }
        }

        // Nationality filter (operations page parity)
        if ($nationality = request('filter_nationality')) {
            $query->where('customer_nationality', $nationality);
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        $tours = Tour::where('is_active', true)->orderBy('name')->get(['id','name']);
        $nationalities = Ticket::whereNotNull('customer_nationality')
            ->distinct()
            ->orderBy('customer_nationality')
            ->pluck('customer_nationality');

        // Distinct countries and cities for filters (from tickets to reflect existing data)
        $countries = Ticket::whereNotNull('tour_country')
            ->distinct()
            ->orderBy('tour_country')
            ->pluck('tour_country');
        $cities = Ticket::whereNotNull('tour_region')
            ->distinct()
            ->orderBy('tour_region')
            ->pluck('tour_region');

        // Onay bekleyen bilet isteklerini getir
        $user = Auth::user();
        $pendingRequests = TicketRequest::where('tour_owner_id', $user->id)
            ->where('status', TicketRequest::STATUS_PENDING)
            ->with(['requester.agency', 'tour'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.tickets.index', compact('tickets', 'perPage', 'tours', 'nationalities', 'countries', 'cities', 'pendingRequests'));
    }

    /**
     * Export filtered tickets to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = Ticket::with(['vehicle', 'tour']);

        if ($tourId = $request->get('filter_tour_id')) {
            $query->where('tour_id', $tourId);
        }
        if ($from = $request->get('filter_from')) {
            $query->whereDate('tour_date', '>=', $from);
        }
        if ($to = $request->get('filter_to')) {
            $query->whereDate('tour_date', '<=', $to);
        }
        $filterCountries = $request->get('filter_countries');
        $filterCountry = $request->get('filter_country');
        if (is_array($filterCountries) && count($filterCountries) > 0) {
            $query->whereIn('tour_country', $filterCountries);
        } elseif (!empty($filterCountry)) {
            $query->where('tour_country', $filterCountry);
        }
        $filterCities = $request->get('filter_cities');
        $filterCity = $request->get('filter_city');
        if (is_array($filterCities) && count($filterCities) > 0) {
            $query->whereIn('tour_region', $filterCities);
        } elseif (!empty($filterCity)) {
            $query->where('tour_region', $filterCity);
        }
        if ($request->filled('filter_is_active')) {
            $query->where('is_active', $request->get('filter_is_active') === '1');
        }
        if ($request->filled('filter_has_vehicle')) {
            if ($request->get('filter_has_vehicle') === '1') {
                $query->whereNotNull('vehicle_id');
            } else {
                $query->whereNull('vehicle_id');
            }
        }
        if ($nationality = $request->get('filter_nationality')) {
            $query->where('customer_nationality', $nationality);
        }
        if ($q = trim($request->get('q', ''))) {
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

        $tickets = $query->orderBy('created_at', 'desc')->get();
        $filename = 'tickets_' . now()->format('Ymd_His') . '.xlsx';

        try {
            // Build XLSX into a temporary file first so any errors are caught here
            $tmpDir = storage_path('app');
            if (!is_dir($tmpDir)) { @mkdir($tmpDir, 0775, true); }
            $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . ('tickets_' . uniqid('', true) . '.xlsx');

            $writer = new \OpenSpout\Writer\XLSX\Writer();
            $writer->openToFile($tmpPath);

            // Sheet settings: column widths (optimized for readability)
            $sheet = $writer->getCurrentSheet();
            $sheet->setColumnWidth(8, 1);   // ID
            $sheet->setColumnWidth(15, 2);  // Takip No
            $sheet->setColumnWidth(15, 3);  // Voucher No
            $sheet->setColumnWidth(22, 4);  // Müşteri Adı
            $sheet->setColumnWidth(15, 5);  // Telefon
            $sheet->setColumnWidth(10, 6);  // Oda No
            $sheet->setColumnWidth(15, 7);  // Milliyet
            $sheet->setColumnWidth(30, 8);  // Tur Adı
            $sheet->setColumnWidth(13, 9);  // Tur Tarihi
            $sheet->setColumnWidth(15, 10); // Ülke
            $sheet->setColumnWidth(15, 11); // Şehir
            $sheet->setColumnWidth(14, 12); // Toplam Fiyat
            $sheet->setColumnWidth(8, 13);  // Para Birimi
            $sheet->setColumnWidth(10, 14); // Aktif
            $sheet->setColumnWidth(15, 15); // Araç Plaka

            // Styles - Professional dark header with white text
            $headerStyle = (new \OpenSpout\Common\Entity\Style\Style())
                ->setFontBold()
                ->setFontSize(12)
                ->setBackgroundColor(\OpenSpout\Common\Entity\Style\Color::toARGB('2C3E50'))  // Dark blue-gray
                ->setFontColor(\OpenSpout\Common\Entity\Style\Color::WHITE)
                ->setCellAlignment(\OpenSpout\Common\Entity\Style\CellAlignment::CENTER)
                ->setCellVerticalAlignment(\OpenSpout\Common\Entity\Style\CellVerticalAlignment::CENTER);
            $evenStyle = (new \OpenSpout\Common\Entity\Style\Style())
                ->setBackgroundColor(\OpenSpout\Common\Entity\Style\Color::toARGB('ECF0F1'));  // Light gray
            $oddStyle = (new \OpenSpout\Common\Entity\Style\Style())
                ->setBackgroundColor(\OpenSpout\Common\Entity\Style\Color::WHITE);

            // Header row with increased height and dark background
            $headersRow = ['ID','Takip No','Voucher No','Müşteri Adı','Telefon','Oda No','Milliyet','Tur Adı','Tur Tarihi','Ülke','Şehir','Toplam Fiyat','Para Birimi','Aktif','Araç Plaka'];
            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues($headersRow, $headerStyle)->setHeight(25));

            // Data rows with zebra striping
            $i = 0;
            foreach ($tickets as $t) {
                $row = [
                    (int) $t->id,
                    (string) $t->tracking_no,
                    (string) $t->voucher_no,
                    (string) $t->customer_name,
                    (string) $t->customer_phone,
                    (string) $t->room_number,
                    (string) $t->nationality_name,
                    (string) $t->tour_name,
                    $t->tour_date ? $t->tour_date->format('Y-m-d') : '',
                    (string) $t->tour_country,
                    (string) $t->tour_region,
                    (float) $t->total_price,
                    (string) $t->currency,
                    $t->is_active ? 'Aktif' : 'Pasif',
                    (string) optional($t->vehicle)->plate_number,
                ];
                $rowStyle = ($i++ % 2 === 0) ? $oddStyle : $evenStyle;
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues($row, $rowStyle)->setHeight(18));
            }

            $writer->close();

            return response()->download($tmpPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            // Log the error and fallback to styled HTML-based XLS (works everywhere)
            \Log::error('Excel export error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $filename = 'tickets_' . now()->format('Ymd_His') . '.xls';
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $html = '<html><head><meta charset="UTF-8">'
                .'<style>table{border-collapse:collapse;width:100%;font-family:Calibri,Arial,sans-serif;font-size:12px} th,td{border:1px solid #d0d7de;padding:6px} thead th{background:#2C3E50;color:#fff;font-weight:700;text-align:center;height:28px} tbody tr:nth-child(odd){background:#ffffff} tbody tr:nth-child(even){background:#ECF0F1} .num{text-align:right} .center{text-align:center}</style>'
                .'</head><body><table><colgroup>'
                .'<col style="width:60px"/><col style="width:130px"/><col style="width:130px"/><col style="width:220px"/><col style="width:120px"/><col style="width:80px"/><col style="width:120px"/><col style="width:260px"/><col style="width:120px"/><col style="width:120px"/><col style="width:140px"/><col style="width:120px"/><col style="width:90px"/><col style="width:80px"/><col style="width:140px"/>'
                .'</colgroup><thead><tr>'
                .'<th>ID</th><th>Takip No</th><th>Voucher No</th><th>Müşteri Adı</th><th>Telefon</th><th>Oda No</th><th>Milliyet</th><th>Tur Adı</th><th>Tur Tarihi</th><th>Ülke</th><th>Şehir</th><th>Toplam Fiyat</th><th>Para Birimi</th><th>Aktif</th><th>Araç Plaka</th>'
                .'</tr></thead><tbody>';
            foreach ($tickets as $t) {
                $html .= '<tr>'
                    .'<td class="center">'.e($t->id).'</td>'
                    .'<td>'.e($t->tracking_no).'</td>'
                    .'<td>'.e($t->voucher_no).'</td>'
                    .'<td>'.e($t->customer_name).'</td>'
                    .'<td>'.e($t->customer_phone).'</td>'
                    .'<td class="center">'.e($t->room_number).'</td>'
                    .'<td>'.e($t->nationality_name).'</td>'
                    .'<td>'.e($t->tour_name).'</td>'
                    .'<td class="center">'.e(optional($t->tour_date)->format('Y-m-d')).'</td>'
                    .'<td>'.e($t->tour_country).'</td>'
                    .'<td>'.e($t->tour_region).'</td>'
                    .'<td class="num">'.e(number_format((float)$t->total_price, 2, '.', '')).'</td>'
                    .'<td class="center">'.e($t->currency).'</td>'
                    .'<td class="center">'.($t->is_active ? 'Aktif' : 'Pasif').'</td>'
                    .'<td>'.e(optional($t->vehicle)->plate_number).'</td>'
                    .'</tr>';
            }
            $html .= '</tbody></table></body></html>';

            $content = "\xEF\xBB\xBF".$html; // UTF-8 BOM to preserve Turkish chars
            return response($content, 200, $headers);
        }
    }

    /**
     * Export filtered tickets to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Ticket::with(['vehicle', 'tour']);

        if ($tourId = $request->get('filter_tour_id')) {
            $query->where('tour_id', $tourId);
        }
        if ($from = $request->get('filter_from')) {
            $query->whereDate('tour_date', '>=', $from);
        }
        if ($to = $request->get('filter_to')) {
            $query->whereDate('tour_date', '<=', $to);
        }
        $filterCountries = $request->get('filter_countries');
        $filterCountry = $request->get('filter_country');
        if (is_array($filterCountries) && count($filterCountries) > 0) {
            $query->whereIn('tour_country', $filterCountries);
        } elseif (!empty($filterCountry)) {
            $query->where('tour_country', $filterCountry);
        }
        $filterCities = $request->get('filter_cities');
        $filterCity = $request->get('filter_city');
        if (is_array($filterCities) && count($filterCities) > 0) {
            $query->whereIn('tour_region', $filterCities);
        } elseif (!empty($filterCity)) {
            $query->where('tour_region', $filterCity);
        }
        if ($request->filled('filter_is_active')) {
            $query->where('is_active', $request->get('filter_is_active') === '1');
        }
        if ($request->filled('filter_has_vehicle')) {
            if ($request->get('filter_has_vehicle') === '1') {
                $query->whereNotNull('vehicle_id');
            } else {
                $query->whereNull('vehicle_id');
            }
        }
        if ($nationality = $request->get('filter_nationality')) {
            $query->where('customer_nationality', $nationality);
        }
        if ($q = trim($request->get('q', ''))) {
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

        $tickets = $query->orderBy('created_at', 'desc')->get();
        $html = view('admin.tickets.pdf', [ 'tickets' => $tickets ])->render();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $filename = 'tickets_' . now()->format('Ymd_His') . '.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tours = Tour::where('is_active', true)
            ->withCount('tickets')
            ->orderByDesc('tickets_count')
            ->orderBy('name')
            ->get();
        
        // Otomatik tarih ve saat (İstanbul saatine göre)
        $defaultDate = \Carbon\Carbon::now('Europe/Istanbul')->format('Y-m-d');
        $defaultTime = \Carbon\Carbon::now('Europe/Istanbul')->format('H:i');
        
        return view('admin.tickets.create', compact('tours', 'defaultDate', 'defaultTime'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
            'pickup_lat' => 'nullable|numeric|between:-90,90',
            'pickup_lng' => 'nullable|numeric|between:-180,180',
            'voucher_no' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('tickets', 'voucher_no')->whereNull('deleted_at')],
        ], [
            'voucher_no.unique' => 'Bu voucher numarası başka bir bilete ait — farklı bir numara giriniz.',
        ]);

        // Otomatik ticket_number oluştur
        $ticketNumber = 'TKT-' . strtoupper(uniqid());

        // Tur bilgilerini al
        $tour = Tour::findOrFail($request->tour_id);
        
        // Seçilen tarihin tur için uygun olup olmadığını kontrol et
        $selectedDate = \Carbon\Carbon::parse($request->tour_date);
        $dayOfWeek = $selectedDate->dayOfWeek; // 0=Pazar, 1=Pazartesi, ...
        
        // Turun uygun günlerini kontrol et
        $availableDays = $tour->available_days ?? [];
        if (!empty($availableDays) && !in_array($dayOfWeek, $availableDays)) {
            return back()->withErrors(['tour_date' => 'Seçilen tarih bu tur için uygun değil.']);
        }

        // Tur tarihi (ve varsa saati) İstanbul saatine göre geçmişte olamaz.
        // pickup_time opsiyonel; boşsa sadece günün sonunu kontrol ederiz.
        $timePart = $request->filled('pickup_time') ? $request->pickup_time : '23:59';
        $tourDateTime = \Carbon\Carbon::parse(
            $request->tour_date . ' ' . $timePart,
            'Europe/Istanbul'
        );
        if ($tourDateTime->lt(\Carbon\Carbon::now('Europe/Istanbul'))) {
            return back()->withErrors(['tour_date' => 'Geçmiş tarihe/saate bilet oluşturulamaz.']);
        }

        // Fiyat hesaplama (sadece takvim fiyatları)
        $prices = method_exists($tour, 'getPricesForDate')
            ? $tour->getPricesForDate($selectedDate)
            : ['adult' => 0, 'child' => 0, 'infant' => 0];
        $adultPrice = $prices['adult'] ?? 0;
        $childPrice = $prices['child'] ?? 0;
        $infantPrice = $prices['infant'] ?? 0;
        
        $adultCount = $request->adult_count;
        $childCount = $request->child_count;
        $infantCount = $request->infant_count;
        
        $totalPrice = ($adultCount * $adultPrice) + ($childCount * $childPrice) + ($infantCount * $infantPrice);
        $deposit = $totalPrice * 0.3;
        $rest = $totalPrice * 0.7;

        // Eğer servis alanı tanımlıysa, konumun poligon içinde olduğundan emin ol
        if ($request->filled('pickup_lat') && $request->filled('pickup_lng')) {
            $areas = $tour->service_areas;
            if ($areas && !$this->pointInsideGeoJson((float)$request->pickup_lat, (float)$request->pickup_lng, $areas)) {
                return back()->withInput()->withErrors(['pickup_location' => 'Seçilen konum, turun servis alanlarının dışında.']);
            }
        }

        // Bilet oluştur
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_nationality' => $request->customer_nationality,
            'tour_id' => $request->tour_id,
            'tour_date' => $request->tour_date,
            'tour_name' => $tour->name,
            'tour_country' => $tour->country,
            'tour_region' => $tour->region ?: ($tour->city ?? 'Bilinmiyor'),
            'pickup_time' => $request->pickup_time,
            'sales_agency' => $tour->sales_agency ?? 'Ana Ofis',
            'total_price' => $totalPrice,
            'deposit' => $deposit,
            'rest' => $rest,
            'currency' => $tour->currency ?? 'TRY',
            'is_active' => true,
            'entry_date' => now()->format('Y-m-d'),
            'entry_time' => now()->format('H:i:s'),
            'pickup_location' => $request->pickup_location,
            'room_number' => $request->room_number,
            'passport_numbers' => $request->passport_numbers,
        ]);

        // Yolcu bilgilerini oluştur
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

        // Konum kaydet
        if ($request->filled('pickup_lat') && $request->filled('pickup_lng')) {
            \App\Models\TicketLocation::updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'latitude' => (float)$request->pickup_lat,
                    'longitude' => (float)$request->pickup_lng,
                    'accuracy' => null,
                ]
            );
        }

        return redirect()->route('admin.tickets.index')->with('success', 'Bilet başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['vehicle', 'passengers', 'tour']);
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        $tours = Tour::where('is_active', true)
            ->withCount('tickets')
            ->orderByDesc('tickets_count')
            ->orderBy('name')
            ->get();
        $ticket->load('passengers');
        return view('admin.tickets.edit', compact('ticket', 'tours'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_nationality' => 'required|string|max:2',
            'tour_id' => 'required|exists:tours,id',
            'tour_date' => 'required|date',
            'adult_count' => 'required|integer|min:0',
            'child_count' => 'required|integer|min:0',
            'infant_count' => 'required|integer|min:0',
            'entry_date' => 'nullable|date',
            'entry_time' => 'nullable',
            'pickup_location' => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:50',
            'passport_numbers' => 'nullable|string',
            'pickup_lat' => 'nullable|numeric|between:-90,90',
            'pickup_lng' => 'nullable|numeric|between:-180,180',
            'pickup_time' => 'nullable|date_format:H:i',
            'voucher_no' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('tickets', 'voucher_no')->ignore($ticket->id)->whereNull('deleted_at')],
        ], [
            'voucher_no.unique' => 'Bu voucher numarası başka bir bilete ait — farklı bir numara giriniz.',
        ]);

        // Tur bilgilerini al
        $tour = Tour::findOrFail($request->tour_id);
        
        // Seçilen tarihin tur için uygun olup olmadığını kontrol et
        $selectedDate = \Carbon\Carbon::parse($request->tour_date);
        $dayOfWeek = $selectedDate->dayOfWeek; // 0=Pazar, 1=Pazartesi, ...
        
        // Turun uygun günlerini kontrol et
        $availableDays = $tour->available_days ?? [];
        if (!empty($availableDays) && !in_array($dayOfWeek, $availableDays)) {
            return back()->withErrors(['tour_date' => 'Seçilen tarih bu tur için uygun değil.']);
        }

        // Not: Geçmiş tarihli biletlerde düzenlemeye izin veriyoruz (operasyonel ihtiyaç)

        // Fiyat hesaplama (sadece takvim fiyatları)
        $prices = method_exists($tour, 'getPricesForDate')
            ? $tour->getPricesForDate($selectedDate)
            : ['adult' => 0, 'child' => 0, 'infant' => 0];
        $adultPrice = $prices['adult'] ?? 0;
        $childPrice = $prices['child'] ?? 0;
        $infantPrice = $prices['infant'] ?? 0;
        
        $adultCount = $request->adult_count;
        $childCount = $request->child_count;
        $infantCount = $request->infant_count;
        
        $totalPrice = ($adultCount * $adultPrice) + ($childCount * $childPrice) + ($infantCount * $infantPrice);
        $deposit = $totalPrice * 0.3;
        $rest = $totalPrice * 0.7;

        // Derived field fallbacks to avoid null constraint violations on tickets table
        $resolvedCountry = $tour->country ?? $ticket->tour_country ?? 'Bilinmiyor';
        $resolvedRegion = $tour->region ?? $ticket->tour_region ?? 'Bilinmiyor';
        // pickup_time artık poligon saatlerinden seçilen değerden gelir; yoksa mevcut değer korunur.
        $resolvedPickupTime = $request->filled('pickup_time')
            ? $request->pickup_time
            : ($ticket->pickup_time ? optional($ticket->pickup_time)->format('H:i') : null);
        $resolvedSalesAgency = $tour->sales_agency ?? $ticket->sales_agency ?? 'Ana Ofis';

        // Servis alanı kontrolü
        if ($request->filled('pickup_lat') && $request->filled('pickup_lng')) {
            $areas = $tour->service_areas;
            if ($areas && !$this->pointInsideGeoJson((float)$request->pickup_lat, (float)$request->pickup_lng, $areas)) {
                return back()->withInput()->withErrors(['pickup_location' => 'Seçilen konum, turun servis alanlarının dışında.']);
            }
        }

        // Bilet güncelle (bilet numarası hariç)
        $ticket->update([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_nationality' => $request->customer_nationality,
            'tour_id' => $request->tour_id,
            'tour_date' => $request->tour_date,
            'tour_name' => $tour->name,
            'tour_country' => $resolvedCountry,
            'tour_region' => $resolvedRegion,
            'pickup_time' => $resolvedPickupTime,
            'sales_agency' => $resolvedSalesAgency,
            'total_price' => $totalPrice,
            'deposit' => $deposit,
            'rest' => $rest,
            'currency' => $tour->currency ?? 'TRY',
            // Diğer alanlar
            'pickup_location' => $request->pickup_location,
            'room_number' => $request->room_number,
            'passport_numbers' => $request->passport_numbers,
            // Giriş bilgilerini otomatik güncelle
            'entry_date' => now()->format('Y-m-d'),
            'entry_time' => now()->format('H:i:s'),
        ]);

        // Mevcut yolcu bilgilerini sil
        $ticket->passengers()->delete();

        // Yeni yolcu bilgilerini oluştur
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

        // Konum kaydet/güncelle
        if ($request->filled('pickup_lat') && $request->filled('pickup_lng')) {
            \App\Models\TicketLocation::updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'latitude' => (float)$request->pickup_lat,
                    'longitude' => (float)$request->pickup_lng,
                    'accuracy' => null,
                ]
            );
        }

        return redirect()->route('admin.tickets.index')->with('success', 'Bilet başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        try {
            $ticket->delete();

            return redirect()->route('admin.tickets.index')
                ->with('success', 'Bilet başarıyla silindi.');

        } catch (\Exception $e) {
            Log::error('Ticket deletion error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Bilet silinirken bir hata oluştu.');
        }
    }

    /**
     * Geometry helper: check if point inside GeoJSON Polygon/MultiPolygon/FeatureCollection.
     */
    private function pointInsideGeoJson(float $lat, float $lng, array $geo): bool
    {
        if (!isset($geo['type'])) return true;
        if ($geo['type'] === 'Polygon') {
            return $this->pointInAnyRing($lat, $lng, $geo['coordinates'] ?? []);
        }
        if ($geo['type'] === 'MultiPolygon') {
            foreach (($geo['coordinates'] ?? []) as $poly) {
                if ($this->pointInAnyRing($lat, $lng, $poly)) return true;
            }
            return false;
        }
        if ($geo['type'] === 'FeatureCollection') {
            $features = $geo['features'] ?? [];
            if (empty($features)) return true;
            foreach ($features as $feat) {
                $g = $feat['geometry'] ?? null;
                if (!is_array($g)) continue;
                if ($this->pointInsideGeoJson($lat, $lng, $g)) return true;
            }
            return false;
        }
        return true;
    }

    private function pointInAnyRing(float $lat, float $lng, array $rings): bool
    {
        if (empty($rings) || empty($rings[0])) return true;
        $inOuter = $this->pointInRing($lat, $lng, $rings[0]);
        if (!$inOuter) return false;
        // holes
        $numRings = count($rings);
        for ($i = 1; $i < $numRings; $i++) {
            if ($this->pointInRing($lat, $lng, $rings[$i])) return false;
        }
        return true;
    }

    // Ray casting; ring is array of [lng,lat]
    private function pointInRing(float $lat, float $lng, array $ring): bool
    {
        $inside = false;
        $n = count($ring);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = (float)($ring[$i][1] ?? 0.0); // lat
            $yi = (float)($ring[$i][0] ?? 0.0); // lng
            $xj = (float)($ring[$j][1] ?? 0.0);
            $yj = (float)($ring[$j][0] ?? 0.0);
            $intersect = (($yi > $lng) != ($yj > $lng)) &&
                         ($lat < ($xj - $xi) * ($lng - $yi) / (($yj - $yi) ?: 1e-12) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }
}
