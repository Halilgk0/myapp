<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehicleLocationController extends Controller
{
    /**
     * Get all vehicle locations
     */
    public function index()
    {
        $locations = VehicleLocation::with('vehicle')
            ->latest()
            ->paginate(50);

        return response()->json($locations);
    }

    /**
     * Update vehicle location
     */
    public function updateLocation(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric|min:0',
            'recorded_at' => 'nullable|date'
        ]);

        try {
            $location = VehicleLocation::create([
                'vehicle_id' => $vehicle->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'speed' => $validated['speed'] ?? null,
                'heading' => $validated['heading'] ?? null,
                'altitude' => $validated['altitude'] ?? null,
                'accuracy' => $validated['accuracy'] ?? null,
                'recorded_at' => $validated['recorded_at'] ?? now(),
            ]);

            Log::info("Vehicle location updated", [
                'vehicle_id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $location
            ]);

        } catch (\Exception $e) {
            Log::error('Vehicle location update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location'
            ], 500);
        }
    }

    /**
     * Get vehicle locations
     */
    public function getLocations(Vehicle $vehicle)
    {
        $locations = $vehicle->locations()
            ->latest()
            ->paginate(100);

        return response()->json($locations);
    }
}