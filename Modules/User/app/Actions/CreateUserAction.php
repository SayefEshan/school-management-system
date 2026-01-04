<?php

namespace Modules\User\Actions;


use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\User\Data\UserData;
use Modules\User\Events\UserCreated;
use Spatie\Permission\Models\Role;

class CreateUserAction
{
    public function execute(UserData $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'email' => $data->email,
                'phone' => $data->phone,
                'password' => Hash::make($data->password),
                'is_active' => $data->is_active,
                'is_verified' => $data->is_verified,
                // Profile fields
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'image' => $data->image,
                'gender' => $data->gender,
            ]);

            // Assign roles
            $roleNames = Role::whereIn('id', $data->roles)->pluck('name')->toArray();
            $user->assignRole($roleNames);

            // Audit role assignment
            $this->auditRoles($user, [], $roleNames);

            // Emit registered event
            event(new Registered($user));
            
            // Emit UserCreated domain event
            event(new UserCreated($user));

            return $user->fresh(['roles']);
        });
    }

    private function auditRoles(User $user, array $originalRoles, array $newRoles): void
    {
        $user->audits()->create([
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => ['roles' => $originalRoles],
            'new_values' => ['roles' => $newRoles],
            'user_id' => Auth::check() ? Auth::id() : null,
            'user_type' => User::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
