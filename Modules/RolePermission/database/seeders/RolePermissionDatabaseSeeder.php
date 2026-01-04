<?php

namespace Modules\RolePermission\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'admin']);

        $system_all_permissions = Permission::pluck('name');
        $system_role = Role::where('name', 'super-admin')->first();
        $system_role->syncPermissions($system_all_permissions);

        DB::table('model_has_roles')->insert([
            [
                'role_id' => 1,
                'model_type' => User::class,
                'model_id' => 1,
            ],
        ]);
    }
}
