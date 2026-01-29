<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\WelcomeUserMail as MailRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new MailRegistered($event->user));
    }
}
