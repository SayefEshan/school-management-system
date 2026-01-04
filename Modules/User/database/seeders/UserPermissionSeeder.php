<?php

namespace Modules\User\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class UserPermissionSeeder extends Seeder
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
                'module_name' => 'User',
                'name' => 'View User'
            ],
            [
                'module_name' => 'User',
                'name' => 'Create User'
            ],
            [
                'module_name' => 'User',
                'name' => 'Edit User'
            ],
            [
                'module_name' => 'User',
                'name' => 'Delete User'
            ],
            [
                'module_name' => 'User',
                'name' => 'User Password Reset'
            ],
            [
                'module_name' => 'User',
                'name' => 'Export User'
            ],
            [
                'module_name' => 'User',
                'name' => 'Import User'
            ],
            [
                'module_name' => 'User',
                'name' => 'View Push Notification'
            ],
            [
                'module_name' => 'User',
                'name' => 'Create Push Notification'
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
