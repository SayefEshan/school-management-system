<?php

namespace Modules\User\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Modules\Otp\Services\OtpService;
use Modules\User\Actions\CreateUserAction;
use Modules\User\Actions\UpdateUserAction;
use Modules\User\Actions\ManageUserAccountAction;
use Modules\User\Data\UserData;
use Modules\User\Notifications\PasswordChanged;
use Modules\User\Rules\EmailOrPhone;
use Modules\User\Rules\PhoneNumber;
use Modules\User\Transformers\UserResource;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function me(Request $request): JsonResponse
    {
        return apiResponse(true, 'User Details', UserResource::make($request->user()));
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string', new EmailOrPhone],
            'password' => 'required',
        ]);
        $emailOrPhone = $data['id'];
        $password = $data['password'];
        $user = User::where('email', $emailOrPhone)->orWhere('phone', $emailOrPhone)->first();
        if ($user && $user->is_active !== true) {
            return apiResponse(false, 'Your account is not active. Please contact support.', null, null, code: 403);
        }
        if (!$user || !Hash::check($password, $user->password)) {
            return apiResponse(false, 'The credentials you entered are incorrect, please try again.', null, null, code: 401);
        }
        $token = $user->createToken('authToken')->plainTextToken;
        return apiResponse(true, 'Login Successful', ['token' => $token, 'user' => UserResource::make($user)]);
    }

    public function register(Request $request, CreateUserAction $action): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'required_without:phone', 'email'],
            'phone' => ['nullable', 'required_without:email', 'string', new PhoneNumber()],
            'phone_code' => ['nullable', 'string', 'required_with:phone'],
            'email_code' => ['nullable', 'string', 'required_with:email'],
            'role' => ['required', 'in:tenant,landlord,service_vendor,agent'],
        ]);

        $role = Role::where('name', $data['role'])->firstOrFail()->id;

        $request->merge([
            'roles' => [$role],
        ]);
        $userData = UserData::from($request);

        // Verify phone code using OTP service
        if (isset($data['phone'])) {
            $phoneVerification = OtpService::verifyOtp($data['phone'], $data['phone_code']);
            if (!$phoneVerification) {
                return apiResponse(false, 'Invalid Phone Verification Code', null, code: 400);
            }
        }

        // Verify email code using OTP service
        if (isset($data['email'])) {
            $emailVerification = OtpService::verifyOtp($data['email'], $data['email_code']);
            if (!$emailVerification) {
                return apiResponse(false, 'Invalid Email Verification Code', null, code: 400);
            }
        }
        
        // Execute action
        $user = $action->execute($userData);

        // Mark email/phone as verified
        if (isset($data['email'])) {
            $user->email_verified_at = now();
            $user->save();
            OtpService::markOtpAsVerified($data['email'], $data['email_code']);
        }
        if (isset($data['phone'])) {
            $user->phone_verified_at = now();
            $user->save();
            OtpService::markOtpAsVerified($data['phone'], $data['phone_code']);
        }

        $token = $user->createToken('authToken')->plainTextToken;
        return apiResponse(true, 'User Created', ['token' => $token, 'user' => UserResource::make($user)]);
    }

    public function update(Request $request, UpdateUserAction $action): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', new PhoneNumber()],
            'phone_code' => ['nullable', 'string', 'required_with:phone'],
            'email_code' => ['nullable', 'string', 'required_with:email'],
            'role' => ['required', 'in:tenant,landlord,service_vendor,agent'],
        ]);

        $roleId = Role::where('name', $data['role'])->firstOrFail()->id;

        $request->merge([
            'roles' => [$roleId],
        ]);
        $userData = UserData::from($request);
        
        // Verify OTP for email/phone changes
        $emailVerified = false;
        $phoneVerified = false;

        if (!empty($data['email'])) {
            $emailVerified = OtpService::verifyOtp($data['email'], $data['email_code'] ?? '');
            if (!$emailVerified) {
                return apiResponse(false, 'Email not verified', null, code: 400);
            }
        }

        if (!empty($userData->phone)) {
            $phoneVerified = OtpService::verifyOtp($userData->phone, $validated['phone_code'] ?? '');
            if (!$phoneVerified) {
                return apiResponse(false, 'Phone not verified', null, code: 400);
            }
        }

        // Execute base profile update
        $user = $action->execute(Auth::user()->id, $userData);

        // Mark contact changes as verified
        if (!empty($userData->email) && $emailVerified) {
            $user->email_verified_at = now();
            $user->save();
            OtpService::markOtpAsVerified($userData->email, $validated['email_code'] ?? null);
        }

        if (!empty($userData->phone) && $phoneVerified) {
            $user->phone_verified_at = now();
            $user->save();
            OtpService::markOtpAsVerified($userData->phone, $validated['phone_code'] ?? null);
        }

        return apiResponse(true, 'Profile updated successfully', UserResource::make($user->fresh()));
    }


    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_type' => 'required|in:email,phone',
            'contact' => ['required', $request->input('contact_type') === 'email' ? 'email' : new PhoneNumber],
            'password' => 'required|confirmed|min:6',
            'code' => 'required|size:6',
        ]);

        // Verify the OTP using OTP service
        $isVerified = OtpService::verifyOtp($data['contact'], $data['code']);
        if (!$isVerified) {
            return apiResponse(false, 'Invalid Verification Code', null, code: 400);
        }

        $user = User::where($data['contact_type'], $data['contact'])->first();
        if (!$user) {
            return apiResponse(false, 'User not found', null, code: 404);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // Mark the OTP as used using OTP service
        OtpService::markOtpAsVerified($data['contact'], $data['code']);

        event(new PasswordReset($user));
        $user->notify(new PasswordChanged());
        return apiResponse(true, 'Password Reset Successful', null);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            // remove firebase tokens
            $request->user()->firebaseTokens()->delete();
            $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
        }

        return apiResponse(true, 'Logout Successful', null);
    }

    /**
     * Manage a user account (reset or delete)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function manageAccount(Request $request, ManageUserAccountAction $action): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|in:reset,delete',
            'user_id' => 'nullable|exists:users,id',
            'password' => 'required',
        ]);

        // Verify current user's password for security
        if (!Hash::check($data['password'], Auth::user()->password)) {
            return apiResponse(false, 'Password is incorrect', null, code: 401);
        }

        // Determine which user to manage
        $userId = $data['user_id'] ?? Auth::id();

        // If trying to manage another user, check permissions
        if ($userId != Auth::id() && !Auth::user()->hasRole(['admin', 'super_admin'])) {
            return apiResponse(false, 'Unauthorized to manage other users', null, code: 403);
        }

        $user = User::findOrFail($userId);
        $result = $action->execute($user, $data['action']);

        if ($result) {
            $message = $data['action'] === 'reset' ? 'Account reset successfully' : 'Account deleted successfully';

            // If the user deleted their own account, invalidate their token
            if ($data['action'] === 'delete' && $userId == Auth::id()) {
                $request->user()->tokens()->delete();
            }

            return apiResponse(true, $message, null);
        }

        return apiResponse(false, 'Failed to ' . $data['action'] . ' account', null, code: 500);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            return apiResponse(false, 'Current password is incorrect', null, code: 400);
        }

        Auth::user()->update([
            'password' => Hash::make($data['password']),
        ]);

        Auth::user()->notify(new PasswordChanged());
        return apiResponse(true, 'Password changed successfully', null);
    }

    /**
     * Social authentication login/register
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function socialAuth(Request $request, \Modules\User\Actions\HandleSocialUserAction $action): JsonResponse
    {
        $data = $request->validate([
            'provider' => 'required|in:google,github,apple',
            'access_token' => 'required|string',
            'role' => 'required|in:tenant,service_vendor,agent,landlord',
        ]);

        $provider = $data['provider'];

        try {
            // Get user from social provider
            // Note: For Apple, you may need to send the ID token instead of access token
            // and verify it separately. This is a simplified implementation.
            if ($provider === 'apple') {
                // Apple Sign In may require different handling
                // The client should send the ID token which needs JWT verification
                $socialUser = Socialite::driver($provider)->stateless()->userFromToken($data['access_token']);
            } else {
                $socialUser = Socialite::driver($provider)->stateless()->userFromToken($data['access_token']);
            }
        } catch (\Exception $e) {
            Log::error('Social auth error: ' . $e->getMessage(), [
                'provider' => $provider,
                'exception' => $e
            ]);
            return apiResponse(false, 'Failed to authenticate with ' . ucfirst($provider) . '. Please check your credentials and try again.', null, null, code: 401);
        }

        // Find or create user
        $user = $action->execute($provider, $socialUser, $data['role'] ?? null);

        if (!$user->is_active) {
            return apiResponse(false, 'Your account is not active. Please contact support.', null, null, code: 403);
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('authToken')->plainTextToken;

        return apiResponse(true, 'Authentication successful', [
            'token' => $token,
            'user' => UserResource::make($user)
        ]);
    }
}
