<?php

namespace Modules\Otp\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Otp\Notifications\SendVerificationCode;
use Modules\Otp\Services\OtpService;
use Modules\Otp\Transformers\VerificationCodeResource;
use Modules\Otp\Models\VerificationCode;
use Modules\User\Rules\PhoneNumber;

class OtpVerificationController extends Controller
{
    /**
     * Send verification code to the provided contact
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_type' => 'required|in:email,phone',
            'contact' => ['required', $request->input('contact_type') === 'email' ? 'email' : new PhoneNumber],
            'is_registration' => 'nullable|boolean',
        ]);

        if (($data['is_registration'] ?? false) && User::where($data['contact_type'], $data['contact'])->exists()) {
            return apiResponse(false, 'An account with this phone number already exists.', null, code: 400);
        }

        // Don't allow to send verification code within 60 seconds
        $olVerificationCode = VerificationCode::active()->contact($data['contact'])->first();
        if ($olVerificationCode && $olVerificationCode->created_at->diffInSeconds(now()) < 60) {
            return apiResponse(false, 'Please wait for 60 seconds before requesting another verification code.', null, 400);
        }

        // Maximum 5 verification code can be sent in 24 hours
        $verificationCodeCount = VerificationCode::active()->contact($data['contact'])->count();
        $maxVerificationAttempts = (int)config('settings.max_verification_attempts.value', 5);
        if ($verificationCodeCount >= $maxVerificationAttempts) {
            return apiResponse(false, 'Maximum verification code limit reached.', null, code: 400);
        }

        try {
            // Generate the OTP using the OtpService
            $verificationCode = OtpService::generateOtp($data['contact'], $data['contact_type']);
            $verificationCode->notify(new SendVerificationCode());
            $verificationCode = VerificationCodeResource::make($verificationCode);

            return apiResponse(true, 'Verification Code Sent', $verificationCode);
        } catch (Exception $e) {
            Log::error($e);
            return apiResponse(false, 'Internal Server Error', null, code: 500);
        }
    }

    /**
     * Verify the provided OTP code
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact' => 'required',
            'code' => 'required',
        ]);

        $verificationCode = VerificationCode::active()->code($data['code'])->contact($data['contact'])->first();

        if (!$verificationCode) {
            return apiResponse(false, 'Invalid Verification Code', false, code: 400);
        }

        return apiResponse(true, 'Verification Code Valid', true, code: 200);
    }
}
