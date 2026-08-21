<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDeviceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:device';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test device creation and check device_id field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a test device...');
        
        try {
            $device = new Device();
            $device->name = 'Test Device';
            $device->device_code = Device::generateDeviceCode(); // Benzersiz bir device_code oluştur
            $device->phone_number = '1234567890';
            $device->save();
            
            $this->info('Device created successfully!');
            $this->info('Device ID: ' . $device->id);
            $this->info('Device Code: ' . $device->device_code);
            $this->info('Device UUID: ' . $device->device_id);
            
            // Log the device details
            Log::info('Test device created', [
                'device' => $device->toArray()
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create device: ' . $e->getMessage());
            Log::error('Test device creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
