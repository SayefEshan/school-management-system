<?php

namespace Modules\ActivityLog\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class ActivityLogPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $permissions = [
            [
                'module_name' => 'Activity Log',
                'name' => 'View Activity Log'
            ],
            [
                'module_name' => 'Activity Log',
                'name' => 'Delete Activity Log'
            ],
            [
                'module_name' => 'Activity Log',
                'name' => 'Export Activity Log'
            ],
            [
                'module_name' => 'Activity Log',
                'name' => 'View Logs'
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
