<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceLocationService
{

    /**
     * Update the location of a device by its code
     *
     * @param string $deviceCode
     * @param array $locationData
     * @return array
     */
    public function updateDeviceLocation(string $deviceCode, array $locationData): array
    {
        try {
            $device = Device::where('device_code', $deviceCode)->first();
            
            if (!$device) {
                return [
                    'success' => false,
                    'message' => 'Device not found',
                    'status' => 404
                ];
            }
            
            // Validate required fields
            if (!isset($locationData['latitude']) || !isset($locationData['longitude'])) {
                return [
                    'success' => false,
                    'message' => 'Latitude and longitude are required',
                    'status' => 400
                ];
            }
            
            // Update device location
            $updateData = [
                'latitude' => (float) $locationData['latitude'],
                'longitude' => (float) $locationData['longitude'],
                'is_online' => true,
                'last_update' => now(),
            ];
            
            // Optional fields
            if (isset($locationData['speed'])) {
                $updateData['speed'] = (float) $locationData['speed'];
            }
            
            if (isset($locationData['heading'])) {
                $updateData['heading'] = (float) $locationData['heading'];
            }
            
            // device_id alanı benzersiz olmalı ve API üzerinden değiştirilmemeli
            // Bu nedenle bu kısmı kaldırıyoruz
            // if (isset($locationData['device_id'])) {
            //     $updateData['device_id'] = Str::limit($locationData['device_id'], 255);
            // }
            
            $device->update($updateData);
            
            Log::info("Device location updated", [
                'device_id' => $device->id,
                'device_code' => $deviceCode,
                'location' => [
                    'latitude' => $updateData['latitude'],
                    'longitude' => $updateData['longitude'],
                    'speed' => $updateData['speed'] ?? null,
                    'heading' => $updateData['heading'] ?? null,
                ]
            ]);
            
            return [
                'success' => true,
                'message' => 'Location updated successfully',
                'device' => $device->fresh(),
                'status' => 200
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to update device location: " . $e->getMessage(), [
                'device_code' => $deviceCode,
                'location_data' => $locationData,
                'exception' => $e
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }
    
    /**
     * Get all devices with their latest locations
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDevicesWithLocations(array $filters = [])
    {
        $query = Device::query();
        
        // Apply filters
        if (!empty($filters['online_only'])) {
            $query->where('is_online', true);
        }
        
        if (!empty($filters['with_location_only'])) {
            $query->whereNotNull('latitude')
                  ->whereNotNull('longitude');
        }
        
        return $query->get();
    }
    
    /**
     * Get a device by its code with location data
     * 
     * @param string $deviceCode
     * @return \App\Models\Device|null
     */
    public function getDeviceByCode(string $deviceCode)
    {
        return Device::where('device_code', $deviceCode)->first();
    }
    
    /**
     * Generate a unique device code
     * 
     * @param int $length
     * @return string
     */
    public function generateDeviceCode(int $length = 5): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (Device::where('device_code', $code)->exists());
        
        return $code;
    }
}
