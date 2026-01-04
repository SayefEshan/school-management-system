<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the provider's authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the provider.
     */
    public function callback(string $provider, \Modules\User\Actions\HandleSocialUserAction $action): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            if ($provider === 'apple') {
                $socialUser = Socialite::driver($provider)->stateless()->user();
            } else {
                $socialUser = Socialite::driver($provider)->user();
            }
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }

        $user = $action->execute($provider, $socialUser);

        if (!$user->is_active) {
            return redirect()->route('login')->with('error', 'Your account is not active. Please contact support.');
        }

        Auth::login($user, true);

        $user->update(['last_login_at' => now()]);

        // Redirect based on user role
        $userRoles = $user->roles->pluck('name')->toArray();
        if (in_array('admin', $userRoles) || in_array('super-admin', $userRoles)) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Validate that the provider is supported.
     */
    protected function validateProvider(string $provider): void
    {
        if (!in_array($provider, ['google', 'github', 'apple'])) {
            abort(404);
        }
    }
}
