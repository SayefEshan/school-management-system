<?php

namespace Modules\Settings\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Settings\Models\Setting;

class ApiSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function appSettings(Request $request): JsonResponse
    {
        // $language = $request->header('Accept-Language');
        $settings = Setting::where('group', 'Mobile App')->get();
        $settings = $settings->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
        return apiResponse(true, "Settings Retrieved..", $settings);
    }
}
