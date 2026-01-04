<?php

namespace Modules\ImportDownloadManager\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ImportDownloadManagerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'module_name' => 'Settings',
                'name' => 'Download Import Manager Management',
            ],
            [
                'module_name' => 'Settings',
                'name' => 'Import Manager Data Download',
            ],
            [
                'module_name' => 'Settings',
                'name' => 'Import Manager Data Delete',
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['module_name' => $permission['module_name'], 'name' => $permission['name']],
                ['guard_name' => 'web']
            );
        }
    }
}
