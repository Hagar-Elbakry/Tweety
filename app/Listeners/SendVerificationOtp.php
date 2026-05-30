<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Mail;

class SendVerificationOtp implements ShouldQueueAfterCommit
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new VerifyEmail($event->user, $event->otpCode));
    }
}
