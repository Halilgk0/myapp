<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = (int) request('per_page', 10);
        if ($perPage <= 0) { $perPage = 10; }

        $query = Vehicle::with(['driver', 'tickets.passengers']);

        // Status filter (map to is_active boolean)
        if ($status = request('filter_status')) {
            if ($status === 'available') {
                $query->where('is_active', true);
            } elseif ($status === 'busy') {
                $query->where('is_active', false);
            }
        }

        // Capacity group filter
        if ($capGroup = request('filter_capacity')) {
            if ($capGroup === 'small') {
                $query->whereBetween('capacity', [1, 8]);
            } elseif ($capGroup === 'medium') {
                $query->whereBetween('capacity', [9, 16]);
            } elseif ($capGroup === 'large') {
                $query->where('capacity', '>=', 17);
            }
        }

        // Driver presence filter (with/without driver)
        if ($driverFilter = request('filter_driver')) {
            if ($driverFilter === 'with') {
                $query->whereNotNull('driver_id');
            } elseif ($driverFilter === 'without') {
                $query->whereNull('driver_id');
            }
        }

        $vehicles = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('admin.vehicles.index', compact('vehicles', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only list drivers who are not assigned to any vehicle
        $drivers = User::where('level', 2)
            ->where('is_active', true)
            ->doesntHave('vehicle')
            ->get();
        return view('admin.vehicles.create', compact('drivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'vehicle_type' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driver_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Ensure unchecked checkbox becomes false
        $validated['is_active'] = $request->boolean('is_active');

        try {
            DB::beginTransaction();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('vehicles', 'public');
                $validated['image'] = $imagePath;
            }

            $vehicle = Vehicle::create($validated);

            DB::commit();

            return redirect()->route('admin.vehicles.index')
                ->with('success', 'Araç başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle creation error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Araç oluşturulurken bir hata oluştu.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load('driver', 'locations');
        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        // Allow selecting current driver or any unassigned driver
        $drivers = User::where('level', 2)
            ->where('is_active', true)
            ->where(function($q) use ($vehicle) {
                $q->doesntHave('vehicle')
                  ->orWhere('id', $vehicle->driver_id);
            })
            ->get();
        return view('admin.vehicles.edit', compact('vehicle', 'drivers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,' . $vehicle->id,
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'vehicle_type' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driver_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Ensure unchecked checkbox becomes false
        $validated['is_active'] = $request->boolean('is_active');

        try {
            DB::beginTransaction();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($vehicle->image) {
                    Storage::disk('public')->delete($vehicle->image);
                }
                $imagePath = $request->file('image')->store('vehicles', 'public');
                $validated['image'] = $imagePath;
            }

            $vehicle->update($validated);

            DB::commit();

            return redirect()->route('admin.vehicles.index')
                ->with('success', 'Araç başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vehicle update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Araç güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            // Delete image if exists
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }

            $vehicle->delete();

            return redirect()->route('admin.vehicles.index')
                ->with('success', 'Araç başarıyla silindi.');

        } catch (\Exception $e) {
            Log::error('Vehicle deletion error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Araç silinirken bir hata oluştu.');
        }
    }

    /**
     * Get vehicle locations for map
     */
    public function locations()
    {
        $vehicles = Vehicle::with(['driver', 'locations' => function($query) {
            $query->latest()->limit(1);
        }])->where('is_active', true)->get();

        $formattedVehicles = $vehicles->map(function ($vehicle) {
            $lastLocation = $vehicle->getCurrentLocationAttribute();
            
            return [
                'id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'driver_name' => $vehicle->driver ? $vehicle->driver->name : 'Atanmamış',
                'lat' => $lastLocation ? $lastLocation->latitude : null,
                'lng' => $lastLocation ? $lastLocation->longitude : null,
                'speed' => $lastLocation ? $lastLocation->speed : null,
                'heading' => $lastLocation ? $lastLocation->heading : null,
                'last_update' => $lastLocation ? $lastLocation->created_at : null,
                'status' => $vehicle->status,
            ];
        });

        return response()->json($formattedVehicles);
    }

    /**
     * Export vehicles to styled XLS (HTML-based) for Excel
     */
    public function exportExcel(Request $request)
    {
        $query = Vehicle::with('driver');

        if ($status = $request->get('filter_status')) {
            if ($status === 'available') { $query->where('is_active', true); }
            elseif ($status === 'busy') { $query->where('is_active', false); }
        }
        if ($capGroup = $request->get('filter_capacity')) {
            if ($capGroup === 'small') { $query->whereBetween('capacity', [1,8]); }
            elseif ($capGroup === 'medium') { $query->whereBetween('capacity', [9,16]); }
            elseif ($capGroup === 'large') { $query->where('capacity', '>=', 17); }
        }
        if ($driverFilter = $request->get('filter_driver')) {
            if ($driverFilter === 'with') { $query->whereNotNull('driver_id'); }
            elseif ($driverFilter === 'without') { $query->whereNull('driver_id'); }
        }

        $vehicles = $query->orderBy('created_at','desc')->get();

        $filename = 'vehicles_'.now()->format('Ymd_His').'.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $html = '<html><head><meta charset="UTF-8">'
            .'<style>table{border-collapse:collapse;width:100%;font-family:Calibri,Arial,sans-serif;font-size:12px} th,td{border:1px solid #d0d7de;padding:6px} thead th{background:#2C3E50;color:#fff;font-weight:700;text-align:center;height:28px} tbody tr:nth-child(even){background:#ECF0F1} .num{text-align:right} .center{text-align:center}</style>'
            .'</head><body><table><colgroup>'
            .'<col style="width:70px"/><col style="width:120px"/><col style="width:120px"/><col style="width:120px"/><col style="width:110px"/><col style="width:90px"/><col style="width:120px"/><col style="width:160px"/><col style="width:90px"/><col style="width:200px"/>'
            .'</colgroup><thead><tr>'
            .'<th>ID</th><th>Plaka</th><th>Marka</th><th>Model</th><th>Tip</th><th>Kapasite</th><th>Renk</th><th>Şoför</th><th>Durum</th><th>Notlar</th>'
            .'</tr></thead><tbody>';
        foreach ($vehicles as $v) {
            $html .= '<tr>'
                .'<td class="center">'.e($v->id).'</td>'
                .'<td>'.e($v->plate_number).'</td>'
                .'<td>'.e($v->brand).'</td>'
                .'<td>'.e($v->model).'</td>'
                .'<td>'.e($v->vehicle_type).'</td>'
                .'<td class="center">'.e($v->capacity).'</td>'
                .'<td>'.e($v->color).'</td>'
                .'<td>'.e(optional($v->driver)->name).'</td>'
                .'<td class="center">'.($v->is_active ? 'Aktif' : 'Pasif').'</td>'
                .'<td>'.e($v->notes).'</td>'
                .'</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return response("\xEF\xBB\xBF".$html, 200, $headers);
    }

    /**
     * Export vehicles to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Vehicle::with('driver');
        $vehicles = $query->orderBy('created_at','desc')->get();
        $html = view('admin.vehicles.pdf', compact('vehicles'))->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $filename = 'vehicles_'.now()->format('Ymd_His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }
}
