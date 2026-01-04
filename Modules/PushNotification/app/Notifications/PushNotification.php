<?php

namespace Modules\PushNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PushNotification extends Notification
{
    use Queueable;

    private string $title;
    private string $body;
    private ?array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $body, ?array $data)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['fcm'];
    }

    public function toFcm(object $notifiable): array
    {
        $tokens = $notifiable->firebaseTokens->pluck('token')->toArray();
        return [
            'to' => $tokens,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
            ],
            'data' => $this->data,
        ];
    }
}
