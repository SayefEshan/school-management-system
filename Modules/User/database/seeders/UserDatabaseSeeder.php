<?php

namespace Modules\User\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $system_user = [
            [
                'first_name' => "Super",
                'last_name' => "Admin",
                'gender' => "Male",
                'email' => 'superadmin@example.com',
                'phone' => '8801234567890',
                'password' => bcrypt('12345678'),
            ],
        ];

        foreach ($system_user as $user) {
            User::firstOrCreate(
                [
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                ],
                $user
            );
        }
    }
}
