<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Artisan::call('module:seed', [
            '--class' => 'RolePermissionDatabaseSeederPermission',
            'module' => 'RolePermission'
        ]);
        Artisan::call('module:seed', [
            '--class' => 'SettingsPermissionSeeder',
            'module' => 'Settings'
        ]);
        Artisan::call('module:seed', [
            '--class' => 'ActivityLogPermissionSeeder',
            'module' => 'ActivityLog'
        ]);
        Artisan::call('module:seed', [
            '--class' => 'UserPermissionSeeder',
            'module' => 'User'
        ]);
        Artisan::call('module:seed', [
            '--class' => 'ImportDownloadManagerPermissionSeeder',
            'module' => 'ImportDownloadManager'
        ]);
        Artisan::call('module:seed', [
            '--class' => 'AdmissionPermissionSeeder',
            'module' => 'Admission'
        ]);
    }
}
