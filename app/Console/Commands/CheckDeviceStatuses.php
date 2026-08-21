<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckDeviceStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:check-statuses 
                            {--minutes=5 : Consider devices offline if last update is older than this value (in minutes)}
                            {--force : Force update all devices}
                            {--dry-run : Run without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update the online/offline status of all devices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        
        $this->info("Checking device statuses (offline threshold: {$minutes} minutes)...");
        
        $query = Device::query();
        
        if (!$force) {
            // Only check devices that are marked as online or have been updated recently
            $query->where(function($q) use ($minutes) {
                $q->where('is_online', true)
                  ->orWhere('last_update', '>=', now()->subMinutes($minutes * 2));
            });
        }
        
        $devices = $query->get();
        
        if ($devices->isEmpty()) {
            $this->info('No devices to check.');
            return 0;
        }
        
        $this->info("Checking status for {$devices->count()} devices...");
        
        $updated = 0;
        $now = now();
        
        foreach ($devices as $device) {
            $wasOnline = (bool) $device->is_online;
            $isOnline = $device->last_update && 
                       $device->last_update->diffInMinutes($now) <= $minutes;
            
            $statusChanged = $wasOnline !== $isOnline;
            
            if ($statusChanged || $force) {
                $this->line(sprintf(
                    'Device #%d: %s -> %s%s',
                    $device->id,
                    $wasOnline ? '<comment>online</comment>' : '<fg=red>offline</>',
                    $isOnline ? '<comment>online</comment>' : '<fg=red>offline</>',
                    $statusChanged ? '' : ' (forced)'
                ));
                
                if (!$dryRun) {
                    $device->is_online = $isOnline;
                    $device->save();
                    $updated++;
                }
            }
        }
        
        if ($dryRun) {
            $this->info("\n[DRY RUN] Would update {$updated} devices.");
        } else {
            $this->info("\nUpdated {$updated} devices.");
        }
        
        Log::info("Device status check completed. Updated {$updated} devices.");
        
        return 0;
    }
}
