<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\UserRegistered as MailRegistered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user)->send(new MailRegistered($event->user));
    }
}
