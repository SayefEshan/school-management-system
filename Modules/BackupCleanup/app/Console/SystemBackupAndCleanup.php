<?php

namespace Modules\BackupCleanup\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\ActivityLog\Models\AnalyticsSession;

class SystemBackupAndCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:backup:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database and clean up old backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->backupAndCleanDB();
        $this->cleanAnalytics();
    }

    private function backupAndCleanDB(): void
    {
        try {
            $this->comment('Starting backup');
            Artisan::call('backup:run --only-db --disable-notifications');
            Log::info('Backup complete with output: ' . Artisan::output());
            $this->comment('Backup complete');

            // clean up old backups
            $this->comment('Cleaning up old backups');
            Artisan::call('backup:clean --disable-notifications');
            Log::info('Clean up complete with output: ' . Artisan::output());
            $this->comment('Clean up complete');
        } catch (\Exception $e) {
            Log::error('Backup failed with error: ' . $e->getMessage());
            $this->error('Backup failed with error: ' . $e->getMessage());
        }
    }

    private function cleanAnalytics(): void
    {
        try {

            $days = (int)config('app.delete_analytics_logs_older_than_days', '0');

            // if $days is 0, do not delete any logs
            if ($days === 0) {
                $this->comment('Not deleting any analytics logs');
                return;
            }

            // select all analytics logs older than $days
            $logs = AnalyticsSession::where('created_at', '<', now()->subDays($days))->get();
            $logs->each(function ($log) {
                $log->delete();
            });

            $this->comment('Deleted ' . $logs->count() . ' analytics logs older than ' . $days . ' days');
        } catch (\Exception $e) {
            Log::error('Analytics cleanup failed with error: ' . $e->getMessage());
        }
    }

}
