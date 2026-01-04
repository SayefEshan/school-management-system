<?php

namespace Modules\Settings\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Models\Setting;

class SettingsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // load config file from config/config.php
        $settings = require __DIR__ . '/../../config/config.php';
        foreach ($settings as $key => $value) {
            $hasAlready = Setting::where('key', $key)->first();
            if (!$hasAlready) {
                $value['key'] = $key;
                Setting::updateOrCreate(['key' => $key], $value);
            }
        }
    }
}
