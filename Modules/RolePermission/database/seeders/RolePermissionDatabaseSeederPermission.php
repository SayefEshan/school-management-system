<?php

namespace Modules\RolePermission\database\seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolePermissionDatabaseSeederPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $permissions = [
            [
                'module_name' => 'Role Management',
                'name' => 'View Role'
            ],
            [
                'module_name' => 'Role Management',
                'name' => 'Create Role'
            ],
            [
                'module_name' => 'Role Management',
                'name' => 'Edit Role'
            ],
            [
                'module_name' => 'Role Management',
                'name' => 'Assign Permission'
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
