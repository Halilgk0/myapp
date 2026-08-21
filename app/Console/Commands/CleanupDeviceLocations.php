<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeviceLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupDeviceLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:cleanup {--days=30 : Number of days to keep location history} {--dry-run : Run without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old device location records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up device locations older than {$cutoffDate->format('Y-m-d')} (keeping {$days} days of history)");
        
        $query = DeviceLocation::where('created_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No old location records found to clean up.');
            return 0;
        }
        
        if ($dryRun) {
            $this->info("[DRY RUN] Would delete {$count} location records older than {$cutoffDate->format('Y-m-d')}");
            return 0;
        }
        
        $this->info("Deleting {$count} location records older than {$cutoffDate->format('Y-m-d')}...");
        
        try {
            $deleted = $query->delete();
            $this->info("Successfully deleted {$deleted} location records.");
            Log::info("Cleaned up {$deleted} old device location records older than {$cutoffDate->format('Y-m-d')}");
        } catch (\Exception $e) {
            $this->error("Error cleaning up device locations: " . $e->getMessage());
            Log::error("Error cleaning up device locations: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
