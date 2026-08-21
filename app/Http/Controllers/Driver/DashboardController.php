<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show driver dashboard
     */
    public function index()
    {
        $driver = Auth::user();
        $vehicle = $driver->vehicle;
        $vehicleId = $vehicle?->id;
        $today = now()->toDateString();

        // Bugün ve gelecek günlerin biletleri (geçmişler dashboard'a girmez).
        $tickets = Ticket::query()
            ->where('is_active', true)
            ->whereDate('tour_date', '>=', $today)
            ->where(function ($q) use ($driver, $vehicleId) {
                $q->where('driver_id', $driver->id);
                if ($vehicleId) {
                    $q->orWhere('vehicle_id', $vehicleId);
                }
            })
            ->with(['tour', 'passengers', 'location'])
            ->orderBy('tour_date')
            ->orderByRaw('route_order IS NULL ASC')
            ->orderBy('route_order')
            ->orderBy('pickup_time')
            ->orderBy('id')
            ->get();

        // Tour_date'e göre grupla
        $ticketsByDay = [];
        foreach ($tickets as $t) {
            $key = $t->tour_date->toDateString();
            if (!isset($ticketsByDay[$key])) {
                $ticketsByDay[$key] = [
                    'date'      => $key,
                    'tickets'   => [],
                    'has_route' => false,
                ];
            }
            $ticketsByDay[$key]['tickets'][] = $t;
            if (!is_null($t->route_order) || $t->is_route_start) {
                $ticketsByDay[$key]['has_route'] = true;
            }
        }
        ksort($ticketsByDay);

        $mapboxToken = config('services.mapbox.access_token');

        return view('driver.dashboard', compact('driver', 'vehicle', 'ticketsByDay', 'today', 'mapboxToken'));
    }

    /**
     * Show driver profile
     */
    public function profile()
    {
        $driver = Auth::user();
        return view('driver.profile', compact('driver'));
    }

    /**
     * Update driver profile
     */
    public function updateProfile(Request $request)
    {
        $driver = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $driver->id,
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $updateData = [
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
            ];

            if ($validated['password']) {
                $updateData['password'] = bcrypt($validated['password']);
            }

            $driver->update($updateData);

            return redirect()->route('driver.profile')
                ->with('success', 'Profil başarıyla güncellendi.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Profil güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Show vehicle details
     */
    public function vehicle()
    {
        $driver = Auth::user();
        $vehicle = $driver->vehicle;
        
        if (!$vehicle) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'Size atanmış bir araç bulunmamaktadır.');
        }
        
        return view('driver.vehicle', compact('vehicle'));
    }

    /**
     * Şoförün canlı konum güncellemesi (web session). Dashboard üzerindeki
     * "Sefere Başla" butonu watchPosition ile periyodik olarak buraya POST eder.
     */
    public function updateLocation(Request $request)
    {
        $driver = Auth::user();
        if (!$driver || (int) $driver->level !== 2) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz'], 403);
        }

        $vehicle = $driver->vehicle;
        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Atanmış araç yok'], 400);
        }

        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
            'accuracy'  => 'nullable|numeric|min:0',
        ]);

        try {
            \App\Models\VehicleLocation::create([
                'vehicle_id'  => $vehicle->id,
                'latitude'    => $validated['latitude'],
                'longitude'   => $validated['longitude'],
                'speed'       => $validated['speed']    ?? null,
                'heading'     => $validated['heading']  ?? null,
                'accuracy'    => $validated['accuracy'] ?? null,
                'recorded_at' => now(),
            ]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::warning('Driver canlı konum kayıt başarısız', [
                'driver_id' => $driver->id, 'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Kayıt başarısız'], 500);
        }
    }

    /**
     * Show assigned tickets — gün bazında gruplanmış, geçmiş + bugün + gelecek.
     */
    public function tickets()
    {
        $driver = Auth::user();
        $vehicle = $driver->vehicle;

        if (!$vehicle) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'Size atanmış bir araç bulunmamaktadır.');
        }

        $vehicleId = $vehicle->id;

        $tickets = \App\Models\Ticket::query()
            ->where('is_active', true)
            ->where(function ($q) use ($driver, $vehicleId) {
                $q->where('driver_id', $driver->id);
                if ($vehicleId) {
                    $q->orWhere('vehicle_id', $vehicleId);
                }
            })
            ->whereNotNull('tour_date')
            ->with(['passengers', 'tour'])
            ->orderBy('tour_date', 'desc')
            ->orderByRaw('route_order IS NULL ASC')
            ->orderBy('route_order')
            ->orderBy('pickup_time')
            ->orderBy('id')
            ->get();

        $today = now()->toDateString();
        $ticketsByDay = [];
        foreach ($tickets as $t) {
            $key = $t->tour_date->toDateString();
            if (!isset($ticketsByDay[$key])) {
                $ticketsByDay[$key] = [
                    'date'             => $key,
                    'tickets'          => [],
                    'is_past'          => $key < $today,
                    'is_today'         => $key === $today,
                    'passenger_count'  => 0,
                ];
            }
            $ticketsByDay[$key]['tickets'][] = $t;
            $ticketsByDay[$key]['passenger_count'] += $t->passengers->sum('quantity');
        }
        // En yeni gün üstte (azalan)
        krsort($ticketsByDay);

        return view('driver.tickets', compact('ticketsByDay', 'vehicle', 'today'));
    }
}
