<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        Artisan::call('module:seed', ['module' => 'RolePermission']);
        Artisan::call('module:seed', ['module' => 'User']);
        Artisan::call('module:seed', ['module' => 'ImportDownloadManager']);
        Artisan::call('module:seed', ['module' => 'Notification']);
        Artisan::call('module:seed', ['module' => 'Settings']);
        Artisan::call('module:seed', ['module' => 'ActivityLog']);
        Artisan::call('module:seed', ['module' => 'BackupCleanup']);
        Artisan::call('module:seed', ['module' => 'PushNotification']);
        Artisan::call('module:seed', ['module' => 'Otp']);
    }
}
