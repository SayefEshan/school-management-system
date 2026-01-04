<?php

namespace Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageUserAccountAction
{
    /**
     * Manage a user account - reset or delete
     *
     * @param User $user
     * @param string $action 'reset' or 'delete'
     * @return bool
     */
    public function execute(User $user, string $action): bool
    {
        if (!in_array($action, ['reset', 'delete'])) {
            return false;
        }

        try {
            return DB::transaction(function () use ($user, $action) {
                // Delete Firebase tokens
                if ($user->firebaseTokens()->exists()) {
                    $user->firebaseTokens()->delete();
                }

                // Delete devices
                if ($user->devices()->exists()) {
                    $user->devices()->delete();
                }

                if ($action === 'reset') {
                    // Reset the user's profile info
                    $user->update([
                        'first_name' => null,
                        'last_name' => '',
                        'image' => null,
                        'gender' => null,
                    ]);

                    return true;
                }

                if ($action === 'delete') {
                    // Delete the user
                    $user->delete();

                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error("Error {$action} user account: " . $e->getMessage());
            return false;
        }
    }
}
