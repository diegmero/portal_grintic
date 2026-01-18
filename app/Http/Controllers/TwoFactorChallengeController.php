<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TwoFactorChallengeController extends Controller
{
    public function create()
    {
        return \Inertia\Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        if ($code = $request->code) {
             $google2fa = new \PragmaRX\Google2FA\Google2FA();
             if ($google2fa->verifyKey(decrypt($user->two_factor_secret), $code)) {
                 $request->session()->put('auth.two_factor_verified', true);
                 return redirect()->intended(route('dashboard'));
             }
        } elseif ($recoveryCode = $request->recovery_code) {
             $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
             if (($key = array_search($recoveryCode, $codes)) !== false) {
                 unset($codes[$key]);
                 $user->forceFill([
                     'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
                 ])->save();
                 
                 $request->session()->put('auth.two_factor_verified', true);
                 return redirect()->intended(route('dashboard'));
             }
        }

        return back()->withErrors(['code' => 'El código de autenticación provisto es inválido.']);
    }
}
