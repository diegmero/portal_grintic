<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLockout
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
    public function handle(Lockout $event): void
    {
        \Illuminate\Support\Facades\DB::table('login_lockouts')->insert([
            'ip_address' => $event->request->ip(),
            'email' => $event->request->input('email'),
            'user_agent' => $event->request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
