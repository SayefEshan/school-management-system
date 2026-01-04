<?php

namespace App\Broadcasting;

use App\Jobs\SendSmsJob;
use App\Models\User;
use Modules\Otp\Models\VerificationCode;

class SmsChannel
{
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
        if (!method_exists($notification, 'toSms')) {
            return false;
        }

        $message = $notification->toSms($notifiable);

        if ($notifiable instanceof VerificationCode) {
            $contact = $notifiable->contact;
        }
        if ($notifiable instanceof User) {
            $contact = $notifiable->phone;
        }

        if (empty($contact)) {
            return true;
        }
        SendSmsJob::dispatch($message, $contact);

        return true;
    }
}
