<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\DriverActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = (int) request('per_page', 10);
        if ($perPage <= 0) { $perPage = 10; }

        $query = User::where('level', 2)->with('vehicle');

        // Status filter
        if ($status = request('filter_status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Vehicle assignment filter
        if ($veh = request('filter_vehicle')) {
            if ($veh === 'with') {
                $query->whereHas('vehicle');
            } elseif ($veh === 'without') {
                $query->whereDoesntHave('vehicle');
            }
        }

        // Basic search by name/email/phone
        if ($search = trim(request('q', ''))) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $drivers = $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('admin.drivers.index', compact('drivers', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availableVehicles = Vehicle::whereNull('driver_id')->where('is_active', true)->get();
        return view('admin.drivers.create', compact('availableVehicles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'supported_nationalities' => 'nullable|array',
            'supported_nationalities.*' => 'string|max:10',
            'is_active' => 'boolean',
            'salary_amount' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|size:3',
            'salary_day' => 'nullable|integer|min:1|max:28',
        ]);

        try {
            DB::beginTransaction();

            $driver = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'plain_password' => $validated['password'], // Encrypted cast — APP_KEY ile korunur
                'level' => 2, // Driver level
                'is_active' => $validated['is_active'] ?? true,
                'supported_nationalities' => $validated['supported_nationalities'] ?? null,
                'salary_amount' => $validated['salary_amount'] ?? 0,
                'salary_currency' => strtoupper($validated['salary_currency'] ?? 'TRY'),
                'salary_day' => $validated['salary_day'] ?? 1,
            ]);

            if ($validated['vehicle_id']) {
                Vehicle::where('id', $validated['vehicle_id'])->update(['driver_id' => $driver->id]);
                
                // Record activity
                $vehicle = Vehicle::find($validated['vehicle_id']);
                DriverActivity::create([
                    'driver_id' => $driver->id,
                    'activity_type' => 'vehicle_assigned',
                    'description' => "Araç atandı: {$vehicle->plate_number}",
                    'metadata' => ['vehicle_plate' => $vehicle->plate_number],
                    'recorded_at' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Şoför başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Driver creation error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Şoför oluşturulurken bir hata oluştu.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $driver)
    {
        if ($driver->level !== 2) {
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Bu kullanıcı bir şoför değil.');
        }

        $driver->load(['vehicle', 'activities' => function($query) {
            $query->latest('recorded_at')->limit(20);
        }]);

        // Gün bazında atanmış biletler (rota alanları + konum dahil)
        $ticketsByDay = [];
        try {
            $tickets = \App\Models\Ticket::where('driver_id', $driver->id)
                ->whereNotNull('tour_date')
                ->with(['tour', 'vehicle', 'passengers', 'location'])
                ->orderBy('tour_date', 'asc')
                ->orderByRaw('route_order IS NULL ASC')
                ->orderBy('route_order')
                ->orderBy('pickup_time')
                ->orderBy('id')
                ->get();
            foreach ($tickets as $t) {
                $key = $t->tour_date ? $t->tour_date->toDateString() : 'belirsiz';
                if (!isset($ticketsByDay[$key])) {
                    $ticketsByDay[$key] = [
                        'date' => $key,
                        'tickets' => [],
                        'passenger_count' => 0,
                        'has_route' => false,
                    ];
                }
                $count = $t->passengers->sum('quantity');
                $ticketsByDay[$key]['tickets'][] = $t;
                $ticketsByDay[$key]['passenger_count'] += $count;
                if (!is_null($t->route_order) || $t->is_route_start) {
                    $ticketsByDay[$key]['has_route'] = true;
                }
            }
            ksort($ticketsByDay);
        } catch (\Exception $e) {
            $ticketsByDay = [];
        }

        $mapboxToken = config('services.mapbox.access_token');

        return view('admin.drivers.show', compact('driver', 'ticketsByDay', 'mapboxToken'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $driver)
    {
        if ($driver->level !== 2) {
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Bu kullanıcı bir şoför değil.');
        }

        $availableVehicles = Vehicle::where(function($query) use ($driver) {
            $query->whereNull('driver_id')->orWhere('driver_id', $driver->id);
        })->where('is_active', true)->get();

        // Count unassigned and inactive vehicles for warning on UI
        $inactiveUnassignedCount = Vehicle::whereNull('driver_id')
            ->where('is_active', false)
            ->count();

        return view('admin.drivers.edit', compact('driver', 'availableVehicles', 'inactiveUnassignedCount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $driver)
    {
        if ($driver->level !== 2) {
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Bu kullanıcı bir şoför değil.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $driver->id,
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $driver->id,
            'password' => 'nullable|string|min:6',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'supported_nationalities' => 'nullable|array',
            'supported_nationalities.*' => 'string|max:10',
            'is_active' => 'boolean',
            'salary_amount' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|size:3',
            'salary_day' => 'nullable|integer|min:1|max:28',
        ]);

        try {
            DB::beginTransaction();

            // Update driver info
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'is_active' => $validated['is_active'] ?? true,
                'supported_nationalities' => $validated['supported_nationalities'] ?? null,
                'salary_amount' => $validated['salary_amount'] ?? 0,
                'salary_currency' => strtoupper($validated['salary_currency'] ?? 'TRY'),
                'salary_day' => $validated['salary_day'] ?? 1,
            ];

            if ($validated['password']) {
                $updateData['password'] = Hash::make($validated['password']);
                $updateData['plain_password'] = $validated['password'];
            }

            $driver->update($updateData);

            // Handle vehicle assignment
            $currentVehicle = Vehicle::where('driver_id', $driver->id)->first();
            
            if ($currentVehicle && $currentVehicle->id != $validated['vehicle_id']) {
                // Remove driver from current vehicle
                $currentVehicle->update(['driver_id' => null]);
                
                // Record activity
                DriverActivity::create([
                    'driver_id' => $driver->id,
                    'activity_type' => 'vehicle_unassigned',
                    'description' => "Araç kaldırıldı: {$currentVehicle->plate_number}",
                    'metadata' => ['vehicle_plate' => $currentVehicle->plate_number],
                    'recorded_at' => now()
                ]);
            }

            if ($validated['vehicle_id']) {
                // Assign to new vehicle
                $newVehicle = Vehicle::find($validated['vehicle_id']);
                $newVehicle->update(['driver_id' => $driver->id]);
                
                // Record activity
                DriverActivity::create([
                    'driver_id' => $driver->id,
                    'activity_type' => 'vehicle_assigned',
                    'description' => "Araç atandı: {$newVehicle->plate_number}",
                    'metadata' => ['vehicle_plate' => $newVehicle->plate_number],
                    'recorded_at' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Şoför başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Driver update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Şoför güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Şoförün şifresini admin tarafından belirlenen yeni değere sıfırlar.
     * Yeni şifre flash session ile döner — sayfada bir kez gösterilir.
     */
    public function resetPassword(Request $request, User $driver)
    {
        if ((int) $driver->level !== (int) User::LEVEL_DRIVER) {
            return back()->with('error', 'Bu kullanıcı bir şoför değil.');
        }

        $validated = $request->validate([
            'new_password' => 'required|string|min:4|max:50',
        ], [
            'new_password.required' => 'Yeni şifre zorunludur.',
            'new_password.min'      => 'Şifre en az 4 karakter olmalıdır.',
        ]);

        $newPlain = $validated['new_password'];

        $driver->update([
            'password'       => Hash::make($newPlain),
            'plain_password' => $newPlain,
            'login_attempts' => 0,
            'is_active'      => true,
        ]);

        return redirect()->route('admin.drivers.show', $driver)
            ->with('reset_password', $newPlain)
            ->with('reset_driver_id', $driver->id)
            ->with('success', 'Şifre güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $driver)
    {
        if ($driver->level !== 2) {
            return redirect()->route('admin.drivers.index')
                ->with('error', 'Bu kullanıcı bir şoför değil.');
        }

        try {
            DB::beginTransaction();

            // Remove from vehicle
            if ($driver->vehicle) {
                $driver->vehicle->update(['driver_id' => null]);
            }

            $driver->delete();

            DB::commit();

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Şoför başarıyla silindi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Driver deletion error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Şoför silinirken bir hata oluştu.');
        }
    }

    /**
     * Export drivers to styled XLS (HTML-based)
     */
    public function exportExcel(Request $request)
    {
        $query = User::where('level', 2)->with(['vehicle', 'guide']);

        if ($status = $request->get('filter_status')) {
            if ($status === 'active') { $query->where('is_active', true); }
            elseif ($status === 'inactive') { $query->where('is_active', false); }
        }
        if ($veh = $request->get('filter_vehicle')) {
            if ($veh === 'with') { $query->whereHas('vehicle'); }
            elseif ($veh === 'without') { $query->whereDoesntHave('vehicle'); }
        }
        if ($search = trim($request->get('q', ''))) {
            $query->where(function($q) use ($search){
                $q->where('name','like',"%{$search}%")
                  ->orWhere('email','like',"%{$search}%")
                  ->orWhere('phone_number','like',"%{$search}%");
            });
        }

        $drivers = $query->orderByDesc('id')->get();

        $filename = 'drivers_'.now()->format('Ymd_His').'.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $html = '<html><head><meta charset="UTF-8">'
            .'<style>table{border-collapse:collapse;width:100%;font-family:Calibri,Arial,sans-serif;font-size:12px} th,td{border:1px solid #d0d7de;padding:6px} thead th{background:#2C3E50;color:#fff;font-weight:700;text-align:center;height:28px} tbody tr:nth-child(even){background:#ECF0F1} .num{text-align:right} .center{text-align:center}</style>'
            .'</head><body><table><colgroup>'
            .'<col style="width:70px"/><col style="width:180px"/><col style="width:220px"/><col style="width:140px"/><col style="width:90px"/><col style="width:140px"/><col style="width:160px"/><col style="width:220px"/><col style="width:150px"/>'
            .'</colgroup><thead><tr>'
            .'<th>ID</th><th>Ad</th><th>Email</th><th>Telefon</th><th>Aktif</th><th>Araç</th><th>Rehber</th><th>Desteklenen Milliyetler</th><th>Son Giriş</th>'
            .'</tr></thead><tbody>';
        foreach ($drivers as $d) {
            $html .= '<tr>'
                .'<td class="center">'.e($d->id).'</td>'
                .'<td>'.e($d->name).'</td>'
                .'<td>'.e($d->email).'</td>'
                .'<td>'.e($d->phone_number).'</td>'
                .'<td class="center">'.($d->is_active ? 'Aktif' : 'Pasif').'</td>'
                .'<td>'.e(optional($d->vehicle)->plate_number).'</td>'
                .'<td>'.e(optional($d->guide)->name).'</td>'
                .'<td>'.e($d->supported_nationalities_names ?? ($d->supported_nationalities ? implode(", ",$d->supported_nationalities) : 'Tüm milliyetler')).'</td>'
                .'<td class="center">'.e(optional($d->last_login_at)->format('Y-m-d H:i')).'</td>'
                .'</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return response("\xEF\xBB\xBF".$html, 200, $headers);
    }

    /**
     * Export drivers to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = User::where('level', 2)->with(['vehicle', 'guide']);
        $drivers = $query->orderByDesc('id')->get();
        $html = view('admin.drivers.pdf', compact('drivers'))->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $filename = 'drivers_'.now()->format('Ymd_His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }
}
