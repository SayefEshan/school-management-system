<?php

namespace Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\User\Data\UserData;
use Modules\User\Events\UserUpdated;
use Modules\User\Events\UserRolesChanged;
use Spatie\Permission\Models\Role;

class UpdateUserAction
{
    public function execute(int $userId, UserData $data): User
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::with(['roles'])->findOrFail($userId);

            // Update user and profile fields
            $user->update([
                'email' => $data->email,
                'phone' => $data->phone,
                'is_active' => $data->is_active,
                'is_verified' => $data->is_verified,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'last_name' => $data->last_name,
                'gender' => $data->gender,
            ]);

            if ($data->image) {
                $user->update(['image' => $data->image]);
            }

            // Sync roles and audit if changed
            $originalRoles = $user->roles->pluck('name')->toArray();
            $roleNames = Role::whereIn('id', $data->roles)->pluck('name')->toArray();
            
            $user->syncRoles($roleNames);
            
            $newRoles = $user->roles->pluck('name')->toArray();
            if ($originalRoles !== $newRoles) {
                $this->auditRoles($user, $originalRoles, $newRoles);
                
                // Emit UserRolesChanged event
                event(new UserRolesChanged($user, $originalRoles, $newRoles));
            }
            
            // Emit UserUpdated event
            event(new UserUpdated($user, [
                'profile_updated' => true,
                'roles_changed' => $originalRoles !== $newRoles,
            ]));

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
