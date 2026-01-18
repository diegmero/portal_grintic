<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = $request->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        if (! $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(\Illuminate\Support\Collection::times(8, function () {
                    return \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10);
                })->all())),
            ])->save();
        }

        return back();
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        if ($google2fa->verifyKey(decrypt($user->two_factor_secret), $request->code)) {
            $user->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            return back()->with('status', 'two-factor-authentication-confirmed');
        }

        return back()->withErrors(['code' => 'El código de autenticación es inválido.']);
    }

    public function disable(Request $request)
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back();
    }

    public function qrCode(Request $request)
    {
        $user = $request->user();
        if(! $user->two_factor_secret) return null;

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            decrypt($user->two_factor_secret)
        );

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        
        return response()->json([
            'svg' => $writer->writeString($qrCodeUrl),
            'secret' => decrypt($user->two_factor_secret)
        ]);
    }

    public function recoveryCodes(Request $request)
    {
         $user = $request->user();
         if(! $user->two_factor_recovery_codes) return [];
         
         return response()->json(json_decode(decrypt($user->two_factor_recovery_codes)));
    }
}
