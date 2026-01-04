<?php

use Modules\Settings\Models\Setting;
use Spatie\Permission\Models\Permission;

if (!function_exists("allPermissions")) {

    function allPermissions()
    {
        return Permission::get();
    }
}
if (!function_exists('getSystemSetting')) {
    function getSystemSetting($key)
    {
        return Setting::where('key', $key)->first()->value ?? null;
    }
}

if (!function_exists('isIndexedArray')) {
    function isIndexedArray(array $array): bool {
        return array_keys($array) === range(0, count($array) - 1);
    }
}
