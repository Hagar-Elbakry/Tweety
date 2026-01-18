<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update($currentUser, User $user): bool
    {
        return $currentUser->is($user);
    }
}
