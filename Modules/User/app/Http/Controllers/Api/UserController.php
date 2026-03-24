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
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', new PhoneNumber()],
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        if (!empty($data['role'])) {
            $roleId = Role::where('name', $data['role'])->firstOrFail()->id;
            $request->merge(['roles' => [$roleId]]);
        }

        $userData = UserData::from($request);
        $user = $action->execute($userData);

        $user->email_verified_at = now();
        $user->save();

        $token = $user->createToken('authToken')->plainTextToken;
        return apiResponse(true, 'User Created', ['token' => $token, 'user' => UserResource::make($user)]);
    }

    public function update(Request $request, UpdateUserAction $action): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', new PhoneNumber()],
        ]);

        $userData = UserData::from($request);

        $user = $action->execute(Auth::user()->id, $userData);

        return apiResponse(true, 'Profile updated successfully', UserResource::make($user->fresh()));
    }


    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_type' => 'required|in:email,phone',
            'contact' => ['required', $request->input('contact_type') === 'email' ? 'email' : new PhoneNumber],
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::where($data['contact_type'], $data['contact'])->first();
        if (!$user) {
            return apiResponse(false, 'User not found', null, code: 404);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        event(new PasswordReset($user));
        $user->notify(new PasswordChanged());
        return apiResponse(true, 'Password Reset Successful', null);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->firebaseTokens()->delete();
            $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
        }

        return apiResponse(true, 'Logout Successful', null);
    }

    /**
     * Manage a user account (reset or delete)
     */
    public function manageAccount(Request $request, ManageUserAccountAction $action): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|in:reset,delete',
            'user_id' => 'nullable|exists:users,id',
            'password' => 'required',
        ]);

        if (!Hash::check($data['password'], Auth::user()->password)) {
            return apiResponse(false, 'Password is incorrect', null, code: 401);
        }

        $userId = $data['user_id'] ?? Auth::id();

        if ($userId != Auth::id() && !Auth::user()->hasRole(['admin', 'super_admin'])) {
            return apiResponse(false, 'Unauthorized to manage other users', null, code: 403);
        }

        $user = User::findOrFail($userId);
        $result = $action->execute($user, $data['action']);

        if ($result) {
            $message = $data['action'] === 'reset' ? 'Account reset successfully' : 'Account deleted successfully';

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
}
