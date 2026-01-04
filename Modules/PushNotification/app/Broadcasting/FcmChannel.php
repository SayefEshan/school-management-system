<?php

namespace Modules\PushNotification\Broadcasting;

use App\Models\User;
use App\Traits\MyGuzzleClient;
use File;
use Google_Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    use MyGuzzleClient;

    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
        return true;
    }

    public function send($notifiable, $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            Log::channel('daily_notification')->error("Notification does not have toFcm method.");
            return false;
        }

        $message = $notification->toFcm($notifiable);
        if (!is_array($message['to']) || empty($message['to'])) {
            return true;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::channel('daily_notification')->error("Failed to retrieve access token.");
            return false;
        }

        // Add debug logging
        $googleProjectId = config('pushnotification.google_project_id');
        $url = "https://fcm.googleapis.com/v1/projects/{$googleProjectId}/messages:send";

        $responses = [];
        $errors = [];
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        foreach ($message['to'] as $to) {
            $data = [
                'message' => [
                    'token' => $to,
                    'notification' => $message['notification'],
                    'data' => $message['data'] ? $this->convertDataValuesToString($message['data']) : null,
                ],
            ];
            try {
                $responses[] = $this->guzzle_post_call_json($data, $url, $headers);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            } catch (GuzzleException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            Log::channel('daily_notification')->error(json_encode([
                'status' => "Failed to send FCM notification.",
                'phone' => $notifiable->phone,
                'message' => $message,
                'errors' => $errors,
            ], JSON_THROW_ON_ERROR));
        }
        if (!empty($responses)) {
            Log::channel('daily_notification')->info(json_encode([
                'status' => "FCM notification sent successfully.",
                'phone' => $notifiable->phone,
                'message' => $message,
                'result' => $responses,
            ], JSON_THROW_ON_ERROR));
        }

        return true;
    }

    private function convertDataValuesToString(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = (string) $value;
        }
        return $result;
    }

    private function getAccessToken()
    {
        $cacheKey = 'firebase_access_token';
        $accessToken = Cache::get($cacheKey);

        if ($accessToken) {
            return $accessToken;
        }

        $credentialsJson = config('settings.firebase_credentials_json.value', null);
        if (!$credentialsJson) {
            Log::channel('daily_notification')->error("Firebase credentials JSON is not set.");
            return null;
        }
        $credentialsPath = storage_path('app/firebase_credentials.json');
        File::put($credentialsPath, $credentialsJson);

        try {
            $client = new Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->addScope('https://www.googleapis.com/auth/cloud-platform'); // Add this line for broader permissions

            $token = $client->fetchAccessTokenWithAssertion();
            $accessToken = $token['access_token'] ?? null;

            if ($accessToken) {
                $expiresIn = $token['expires_in'] ?? 3600;
                Cache::put($cacheKey, $accessToken, $expiresIn);
            }

            return $accessToken;
        } catch (\Exception $e) {
            Log::channel('daily_notification')->error("Failed to get access token: " . $e->getMessage());
            return null;
        }
    }
}
