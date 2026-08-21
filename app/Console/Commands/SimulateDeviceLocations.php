<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimulateDeviceLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:device-locations 
                            {--device= : ID of the device to simulate}
                            {--count=10 : Number of locations to generate}
                            {--interval=5 : Interval between locations in seconds}
                            {--base-lat=41.0082 : Base latitude}
                            {--base-lng=28.9784 : Base longitude}
                            {--radius=1 : Radius in kilometers to generate points within}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate device location updates for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceId = $this->option('device');
        $count = (int) $this->option('count');
        $interval = (int) $this->option('interval');
        $baseLat = (float) $this->option('base-lat');
        $baseLng = (float) $this->option('base-lng');
        $radius = (float) $this->option('radius');
        
        // If no device ID provided, list available devices
        if (!$deviceId) {
            $devices = Device::all(['id', 'name', 'device_code', 'is_online']);
            
            if ($devices->isEmpty()) {
                $this->error('No devices found. Please create a device first.');
                return 1;
            }
            
            $this->info('Available devices:');
            $this->table(
                ['ID', 'Name', 'Device Code', 'Status'],
                $devices->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'name' => $device->name,
                        'device_code' => $device->device_code,
                        'status' => $device->is_online ? 'Online' : 'Offline',
                    ];
                })
            );
            
            $deviceId = $this->ask('Enter the ID of the device to simulate');
        }
        
        // Find the device
        $device = Device::find($deviceId);
        
        if (!$device) {
            $this->error("Device with ID {$deviceId} not found.");
            return 1;
        }
        
        $this->info("Simulating {$count} location updates for device: {$device->name} (ID: {$device->id})");
        $this->info("Base location: {$baseLat}, {$baseLng}");
        $this->info("Radius: {$radius} km | Interval: {$interval} seconds");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        for ($i = 0; $i < $count; $i++) {
            try {
                // Generate a random point within the specified radius
                $location = $this->generateRandomPoint([$baseLat, $baseLng], $radius);
                
                $data = [
                    'latitude' => $location[0],
                    'longitude' => $location[1],
                    'speed' => rand(0, 120),
                    'heading' => rand(0, 359),
                    'accuracy' => rand(1, 50) / 10, // 0.1 to 5.0 meters
                    'altitude' => rand(0, 1000),
                    'recorded_at' => now()->toIso8601String(),
                ];
                
                // Call the API
                $response = Http::post(url("/api/devices/{$device->id}/location"), $data);
                
                if ($response->successful()) {
                    $this->line("\n<info>Update #" . ($i + 1) . ":</info> " . 
                              "Lat: {$data['latitude']}, Lng: {$data['longitude']} | " .
                              "Speed: {$data['speed']} km/h | " .
                              "Heading: {$data['heading']}°");
                } else {
                    $this->line("\n<error>Error updating location:</error> " . $response->body());
                }
                
                $bar->advance();
                
                if ($i < $count - 1) {
                    sleep($interval);
                }
                
            } catch (\Exception $e) {
                Log::error('Error in location simulation: ' . $e->getMessage());
                $this->line("\n<error>Error:</error> " . $e->getMessage());
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("Simulation complete!");
        
        return 0;
    }
    
    /**
     * Generate a random point within a given radius of a center point.
     *
     * @param array $center [lat, lng]
     * @param float $radius Radius in kilometers
     * @return array [lat, lng]
     */
    private function generateRandomPoint($center, $radius)
    {
        $radiusInDegrees = $radius / 111.32; // Convert km to degrees (approximate)
        
        // Generate random angle and distance
        $angle = deg2rad(mt_rand(0, 359));
        $distance = sqrt(mt_rand() / mt_getrandmax()) * $radiusInDegrees;
        
        // Calculate new point
        $lat = $center[0] + $distance * cos($angle);
        $lng = $center[1] + $distance * sin($angle) / cos(deg2rad($center[0]));
        
        return [$lat, $lng];
    }
}
