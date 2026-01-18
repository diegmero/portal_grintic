<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginLockoutController extends Controller
{
    public function index()
    {
        $lockouts = \Illuminate\Support\Facades\DB::table('login_lockouts')
            ->orderByDesc('created_at')
            ->paginate(20);

        return \Inertia\Inertia::render('Admins/Lockouts/Index', [
            'lockouts' => $lockouts,
        ]);
    }
}
