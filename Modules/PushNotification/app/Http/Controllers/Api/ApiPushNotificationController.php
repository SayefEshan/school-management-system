<?php

namespace Modules\PushNotification\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PushNotification\Models\FirebaseToken;
use Modules\PushNotification\Notifications\PushNotification;

class ApiPushNotificationController extends Controller
{

    public function updateFirebaseToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required',
            'device_id' => 'required',
        ]);
        $user = $request->user();
        $token = FirebaseToken::updateOrCreate(
            [
                'token' => $data['token'],
                'device_id' => $data['device_id'],
            ],
            [
                'user_id' => $user->id,
            ]
        );
        return apiResponse(true, 'Firebase Token Updated', $token);
    }

    public function sendFcm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'data' => 'nullable|array',
        ]);
        $user = $request->user();
        $user->notify(new PushNotification($data['title'], $data['body'], $data['data'] ?? null));
        // NotificationService::send($user, $data['title'], $data['body'], $data['data'] ?? null);
        return apiResponse(true, 'Notification Sent', null);
    }
}
