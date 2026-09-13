<?php

namespace App\Services;

use App\Models\User;

class BlockService
{
    public function block(User $blocker, User $blocked): void
    {
        $isBlocked = $blocker->blockedUsers()->where('blocked_id', $blocked->id)->exists();
        if (!$isBlocked) {
            $blocker->blockedUsers()->attach($blocked->id);
        }
    }

    public function unblock(User $blocker, User $blocked): void
    {
        $isBlocked = $blocker->blockedUsers()->where('blocked_id', $blocked->id)->exists();
        if ($isBlocked) {
            $blocker->blockedUsers()->detach($blocked->id);
        }
    }
}
