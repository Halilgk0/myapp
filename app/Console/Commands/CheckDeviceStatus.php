<?php

namespace App\Console\Commands;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckDeviceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:check-status 
                            {--minutes=5 : Consider devices offline after this many minutes of inactivity}
                            {--chunk=100 : Number of records to process at a time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update the online status of all devices based on their last update time.';

    /**
     * Execute the console command.
     */
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $chunkSize = (int) $this->option('chunk');
        $cutoffTime = now()->subMinutes($minutes);
        
        $this->info("Checking device statuses (considering devices offline after {$minutes} minutes of inactivity)...");
        
        $totalDevices = Device::count();
        $updatedCount = 0;
        $onlineCount = 0;
        $offlineCount = 0;
        
        $bar = $this->output->createProgressBar($totalDevices);
        $bar->start();
        
        // Process devices in chunks to reduce memory usage
        Device::chunk($chunkSize, function ($devices) use ($cutoffTime, &$updatedCount, &$onlineCount, &$offlineCount, $bar) {
            foreach ($devices as $device) {
                $wasOnline = $device->is_online;
                $isNowOnline = $device->last_update && $device->last_update->gte($cutoffTime);
                
                // Only update if the status has changed
                if ($wasOnline !== $isNowOnline) {
                    $device->is_online = $isNowOnline;
                    $device->save();
                    $updatedCount++;
                    
                    // Log the status change
                    Log::info(sprintf(
                        'Device ID %d status changed to %s (last update: %s)',
                        $device->id,
                        $isNowOnline ? 'online' : 'offline',
                        $device->last_update ? $device->last_update->toDateTimeString() : 'never'
                    ));
                }
                
                if ($isNowOnline) {
                    $onlineCount++;
                } else {
                    $offlineCount++;
                }
                
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        
        // Output summary
        $this->info("Device status check completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Devices', $totalDevices],
                ['Online', $onlineCount],
                ['Offline', $offlineCount],
                ['Status Changes', $updatedCount],
            ]
        );
        
        return Command::SUCCESS;
    }
}
