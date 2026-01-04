<?php

namespace Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class HandleSocialUserAction
{
    /**
     * Find or create a user based on social provider data.
     *
     * @param string $provider
     * @param mixed $socialUser
     * @param string|null $role
     * @return User
     */
    public function execute(string $provider, $socialUser, ?string $role = null): User
    {
        // Extract name
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
        $names = explode(' ', $name, 2);
        $firstName = $names[0] ?? '';
        $lastName = $names[1] ?? '';

        // Check if user exists with this provider OR email
        $user = User::where(function ($query) use ($provider, $socialUser) {
            $query->where('provider', $provider)
                  ->where('provider_id', $socialUser->getId());
        })
        ->orWhere('email', $socialUser->getEmail())
        ->first();

        if ($user) {
            $updateData = [];

            // If found by Email (provider mismatch), link the account
            if ($user->provider_id !== $socialUser->getId() || $user->provider !== $provider) {
                $updateData['provider'] = $provider;
                $updateData['provider_id'] = $socialUser->getId();
            }

            // Update personal details ONLY if local fields are empty
            if (empty($user->first_name)) {
                $updateData['first_name'] = $firstName;
            }
            if (empty($user->last_name)) {
                $updateData['last_name'] = $lastName;
            }
            if (empty($user->image)) {
                $updateData['image'] = $socialUser->getAvatar();
            }
            
            // Verify email if not already verified
            if (!$user->email_verified_at) {
                $updateData['email_verified_at'] = now();
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            return $user;
        }

        // Create new user
        $user = User::create([
            'email' => $socialUser->getEmail(),
            'email_verified_at' => $socialUser->getEmail() ? now() : null,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'image' => $socialUser->getAvatar(),
            'password' => bcrypt(Str::random(32)), // Random password for social users
            'is_active' => true,
            'first_name' => $firstName,
            'last_name' => $lastName ?: '',
        ]);

        // Assign role
        $roleName = $role ?? 'user';
        $roleModel = Role::where('name', $roleName)->first();
        if ($roleModel) {
            $user->assignRole($roleModel);
        } else {
            // Fallback to default user role if requested role not found
            $defaultRole = Role::where('name', 'user')->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole);
            }
        }

        return $user;
    }
}
