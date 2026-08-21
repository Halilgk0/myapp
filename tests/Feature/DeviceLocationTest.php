<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\DeviceLocation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceLocationTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_can_create_a_device_location()
    {
        $device = Device::factory()->create();
        
        $location = DeviceLocation::create([
            'device_id' => $device->id,
            'latitude' => 41.0082,
            'longitude' => 28.9784,
            'speed' => 50.5,
            'heading' => 180,
            'altitude' => 100.5,
            'accuracy' => 5.0,
            'recorded_at' => now(),
        ]);
        
        $this->assertDatabaseHas('device_locations', [
            'id' => $location->id,
            'device_id' => $device->id,
            'latitude' => 41.0082,
            'longitude' => 28.9784,
        ]);
    }
    
    /** @test */
    public function it_can_update_device_location_via_api()
    {
        $device = Device::factory()->create();
        
        $response = $this->postJson("/api/devices/{$device->id}/location", [
            'latitude' => 41.0082,
            'longitude' => 28.9784,
            'speed' => 60.5,
            'heading' => 270,
            'altitude' => 150.2,
            'accuracy' => 3.5,
            'recorded_at' => now()->toIso8601String(),
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Location updated successfully',
                ]);
        
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'latitude' => 41.0082,
            'longitude' => 28.9784,
            'speed' => 60.5,
            'heading' => 270,
            'is_online' => true,
        ]);
        
        $this->assertDatabaseHas('device_locations', [
            'device_id' => $device->id,
            'latitude' => 41.0082,
            'longitude' => 28.9784,
        ]);
    }
    
    /** @test */
    public function it_can_get_device_location_history()
    {
        $device = Device::factory()->create();
        
        // Create some location history
        DeviceLocation::factory()->count(5)->create([
            'device_id' => $device->id,
            'created_at' => now()->subMinutes(10),
        ]);
        
        $response = $this->getJson("/api/devices/{$device->id}/locations");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'device_id' => $device->id,
                        'total_locations' => 5,
                    ]
                ])
                ->assertJsonCount(5, 'data.locations');
    }
    
    /** @test */
    public function it_can_get_device_status()
    {
        $device = Device::factory()->create([
            'is_online' => true,
            'last_update' => now(),
        ]);
        
        // Create a recent location
        $location = DeviceLocation::factory()->create([
            'device_id' => $device->id,
            'created_at' => now(),
        ]);
        
        $response = $this->getJson("/api/devices/{$device->id}/status");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'device_id' => $device->id,
                        'is_online' => true,
                        'current_location' => [
                            'latitude' => (string) $location->latitude,
                            'longitude' => (string) $location->longitude,
                        ]
                    ]
                ]);
    }
}
