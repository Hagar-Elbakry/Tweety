<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update($currentUser, User $user): bool
    {
        return $currentUser->is($user);
    }
}
