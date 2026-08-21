<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\DeviceController;
use App\Models\Device;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDeviceControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:device-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test DeviceController store method';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing DeviceController and DeviceLocationService...');
        
        try {
            // 1. Önce bir cihaz oluşturalım
            $this->info('1. Creating a test device...');
            
            // Simüle edilmiş bir request oluştur
            $request = new Request([
                'name' => 'Test Device From Controller',
                'phone_number' => '5551234567',
                'notes' => 'Test notes',
                'is_active' => true,
                'user_name' => 'Test User',
                'email' => 'testuser' . time() . '@example.com',
                'user_password' => 'password123',
            ]);
            
            // DeviceLocationService oluştur
            $deviceService = new \App\Services\DeviceLocationService();
            
            // DeviceController'ı oluştur
            $controller = new DeviceController($deviceService);
            
            // store metodunu çağır
            DB::beginTransaction();
            $response = $controller->store($request);
            DB::commit();
            
            // Sonuçları göster
            $this->info('Device created successfully!');
            
            // En son oluşturulan cihazı bul
            $device = Device::latest()->first();
            $user = User::where('email', $request->email)->first();
            
            if ($device) {
                $this->info('Device ID: ' . $device->id);
                $this->info('Device Name: ' . $device->name);
                $this->info('Device Code: ' . $device->device_code);
                $this->info('Device UUID: ' . $device->device_id);
            }
            
            if ($user) {
                $this->info('User ID: ' . $user->id);
                $this->info('User Name: ' . $user->name);
                $this->info('User Email: ' . $user->email);
            }
            
            // 2. Şimdi cihazın konumunu güncelleyelim
            $this->info('\n2. Testing DeviceLocationService updateDeviceLocation method...');
            
            if ($device) {
                $locationData = [
                    'latitude' => 41.0082,
                    'longitude' => 28.9784,
                    'speed' => 5.5,
                    'heading' => 180.0
                ];
                
                $result = $deviceService->updateDeviceLocation($device->device_code, $locationData);
                
                $this->info('Location update result: ' . ($result['success'] ? 'Success' : 'Failed'));
                $this->info('Message: ' . $result['message']);
                
                // Güncellenmiş cihazı tekrar çek
                $updatedDevice = Device::find($device->id);
                
                if ($updatedDevice) {
                    $this->info('Updated Device Info:');
                    $this->info('Latitude: ' . $updatedDevice->latitude);
                    $this->info('Longitude: ' . $updatedDevice->longitude);
                    $this->info('Speed: ' . $updatedDevice->speed);
                    $this->info('Heading: ' . $updatedDevice->heading);
                    $this->info('Is Online: ' . ($updatedDevice->is_online ? 'Yes' : 'No'));
                    $this->info('Device UUID: ' . $updatedDevice->device_id);
                }
            }
            
            // Log the device details
            Log::info('Test device created and location updated', [
                'device' => $device ? Device::find($device->id)->toArray() : null,
                'user' => $user ? $user->toArray() : null
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to create device: ' . $e->getMessage());
            Log::error('Test device creation from controller failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
