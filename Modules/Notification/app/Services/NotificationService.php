<?php

namespace Modules\Notification\Services;

use Illuminate\Support\Facades\Notification as LaravelNotification;
use Modules\Notification\Models\Notification;
use Modules\PushNotification\Notifications\PushNotification;

class NotificationService
{
    /**
     * Send both in-app and push notification
     *
     * @param object $notifiable The user or entity to notify
     * @param string $title Notification title
     * @param string $body Notification body text
     * @param array $data Additional data to include
     * @param string $type Notification type
     * @return Notification
     */
    public static function send($notifiable, string $title, string $body, array $data = [], string $type = 'info')
    {
        // Create in-app notification record
        $notification = new Notification();
        $notification->notifiable_type = get_class($notifiable);
        $notification->notifiable_id = $notifiable->id;
        $notification->type = $type;
        $notification->data = [
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ];
        $notification->save();

        // Only send push notification if the user has Firebase tokens
        if (method_exists($notifiable, 'firebaseTokens') && $notifiable->firebaseTokens()->exists()) {
            // Send push notification via Firebase
            LaravelNotification::send($notifiable, new PushNotification(
                $title,
                $body,
                array_merge($data, [
                    'notification_id' => $notification->id,
                    'type' => $type
                ])
            ));
        }

        return $notification;
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param object $notifiable
     * @return int Number of notifications marked as read
     */
    public static function markAllAsRead($notifiable)
    {
        return Notification::where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
