<?php

namespace Modules\ActivityLog\database\seeders;

use Illuminate\Database\Seeder;

class ActivityLogDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ActivityLogPermissionSeeder::class
        ]);
    }
}
