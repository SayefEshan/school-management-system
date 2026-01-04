<?php

namespace Modules\Settings\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SettingsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'module_name' => 'Settings',
                'name' => 'Edit System Setting'
            ],
            [
                'module_name' => 'Settings',
                'name' => 'Edit Special Setting'
            ],
            [
                'module_name' => 'Settings',
                // Developer Setting is used for developer to manage the settings (create, edit, delete)
                'name' => 'Developer Setting'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['module_name' => $permission['module_name'], 'name' => $permission['name']],
                ['guard_name' => 'web']
            );
        }
    }
}
