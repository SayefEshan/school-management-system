<?php

namespace Modules\Otp\Notifications;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Modules\Otp\Services\OtpService;

class SendVerificationCode extends Notification
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
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Check if we should actually send the notification
        // If the recipient is whitelisted, don't send anything
        if (!OtpService::shouldSendOtpNotification($notifiable->contact, $notifiable->contact_type)) {
            Log::channel('daily')->info("Skipping OTP notification for whitelisted " . $notifiable->contact_type . ": " . $notifiable->contact);
            return [];
        }

        if ($notifiable->contact_type === 'email') {
            return ['mail'];
        }
        if ($notifiable->contact_type === 'phone') {
            return ['sms'];
        }
        return [];
    }

    /**
     * Get the mail representation of the notification.
     * @throws Exception
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::channel('daily_email')->info("Sending verification code email to: " . $notifiable->contact . " Code: " . $notifiable->code);
        return (new MailMessage)
            ->subject('Verification Code Email')
            ->view('otp::emails.verification-code', ['code' => $notifiable->code]);
    }

    public function toSms(object $notifiable): string
    {
        return "Your verification code is: $notifiable->code";
    }
}
