<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VehicleLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DriverLocationController extends Controller
{
    /**
     * Update driver location
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->level !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $vehicle = $user->vehicle;
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'No vehicle assigned'
            ], 400);
        }

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

            Log::info("Driver location updated", [
                'driver_id' => $user->id,
                'driver_name' => $user->name,
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
            Log::error('Driver location update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location'
            ], 500);
        }
    }
} 