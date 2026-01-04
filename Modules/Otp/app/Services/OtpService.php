<?php

namespace Modules\Otp\Services;

use Modules\Otp\Models\OtpWhitelist;
use Modules\Otp\Models\VerificationCode;

class OtpService
{
    /**
     * Generate an OTP for the given recipient
     * This will first check if the recipient is whitelisted, and if so,
     * return the fixed OTP instead of generating a new one
     *
     * @param string $recipient
     * @param string $recipientType
     * @return VerificationCode
     */
    public static function generateOtp(string $recipient, string $recipientType): VerificationCode
    {
        // Check if the recipient is whitelisted
        $whitelistedOtp = OtpWhitelist::findByRecipient($recipientType, $recipient);

        // Create a new verification code entry
        $verificationCode = new VerificationCode();
        $verificationCode->contact_type = $recipientType;
        $verificationCode->contact = $recipient;
        $verificationCode->expires_at = now()->addMinutes(30);

        // If whitelisted, use the fixed OTP
        if ($whitelistedOtp) {
            $verificationCode->code = $whitelistedOtp->fixed_otp;
        } else {
            // Otherwise generate a random 6-digit OTP
            $verificationCode->code = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        }

        $verificationCode->save();

        return $verificationCode;
    }

    /**
     * Determine if a recipient should actually receive an OTP notification
     * Whitelisted recipients don't need to receive actual notifications
     *
     * @param string $recipient
     * @param string $recipientType
     * @return bool
     */
    public static function shouldSendOtpNotification(string $recipient, string $recipientType): bool
    {
        // If the recipient is whitelisted, don't send an actual notification
        return !OtpWhitelist::findByRecipient($recipientType, $recipient);
    }

    /**
     * Verify if an OTP code is valid for a given recipient
     *
     * @param string $recipient
     * @param string $code
     * @return bool
     */
    public static function verifyOtp(string $recipient, string $code): bool
    {
        $verificationCode = VerificationCode::active()
            ->code($code)
            ->contact($recipient)
            ->first();

        return $verificationCode !== null;
    }

    /**
     * Mark an OTP code as verified for a given recipient
     *
     * @param string $recipient
     * @param string $code
     * @return void
     */
    public static function markOtpAsVerified(string $recipient, string $code): void
    {
        $verificationCode = VerificationCode::active()
            ->code($code)
            ->contact($recipient)
            ->first();

        if ($verificationCode) {
            $verificationCode->markAsVerified();
        }
    }
}
