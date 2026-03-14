<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('follow-notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
