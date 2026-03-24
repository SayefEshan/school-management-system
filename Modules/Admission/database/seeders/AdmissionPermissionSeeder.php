<?php

namespace Modules\Admission\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdmissionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Academic Structure
            'academic-year.view',
            'academic-year.create',
            'academic-year.edit',
            'academic-year.delete',
            'class.view',
            'class.create',
            'class.edit',
            'class.delete',
            'section.view',
            'section.create',
            'section.edit',
            'section.delete',
            // Admission Applications
            'admission.view',
            'admission.create',
            'admission.edit',
            'admission.delete',
            'admission.accept',
            'admission.reject',
            'admission.review',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
