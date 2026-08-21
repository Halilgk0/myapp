<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleGpsController extends Controller
{
    /**
     * GPS verilerini alıp veritabanına kaydeden endpoint
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'plate_number' => 'required|string|exists:vehicles,plate_number',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $vehicle = Vehicle::where('plate_number', $request->plate_number)->first();
        
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Araç bulunamadı'
            ], 404);
        }

        $vehicle->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => true, // Konum güncellendiğinde aracı aktif olarak işaretle
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Araç konumu güncellendi',
            'data' => [
                'id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'latitude' => $vehicle->latitude,
                'longitude' => $vehicle->longitude,
            ]
        ]);
    }
}