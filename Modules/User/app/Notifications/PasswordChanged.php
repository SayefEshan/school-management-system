<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        // Only include FCM channel if user has firebase tokens
        $channels = ['mail', 'database'];

        if (method_exists($notifiable, 'firebaseTokens') && $notifiable->firebaseTokens()->exists()) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Changed')
            ->line('Your password has been changed.')
            ->line('If you did not change your password, please contact support immediately.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Password Changed',
            'body' => 'Your password has been changed.',
        ];
    }

    /**
     * Get FCM representation of the notification
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toFcm($notifiable)
    {
        $tokens = $notifiable->firebaseTokens->pluck('token')->toArray();

        return [
            'to' => $tokens,
            'notification' => [
                'title' => 'Password Changed',
                'body' => 'Your password has been changed.',
            ],
            'data' => [
                'message' => 'Your password has been changed.',
            ],
        ];
    }
}
