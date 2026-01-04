<?php

namespace Modules\Otp\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class OtpPermissionSeeder extends Seeder
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
                'module_name' => 'Otp',
                'name' => 'View OTP Whitelist'
            ],
            [
                'module_name' => 'Otp',
                'name' => 'Create OTP Whitelist'
            ],
            [
                'module_name' => 'Otp',
                'name' => 'Edit OTP Whitelist'
            ],
            [
                'module_name' => 'Otp',
                'name' => 'Delete OTP Whitelist'
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
