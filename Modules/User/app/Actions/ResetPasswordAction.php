<?php

namespace Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\User\Notifications\PasswordChanged;

class ResetPasswordAction
{
    public function execute(User $user): string
    {
        // Generate random password
        $password = random_int(10000000, 99999999);
        
        // Update password
        $user->password = Hash::make($password);
        $user->save();

        // Send notification
        $user->notify(new PasswordChanged());

        return (string) $password;
    }
}
