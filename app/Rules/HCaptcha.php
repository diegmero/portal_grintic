<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class HCaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret' => env('HCAPTCHA_SECRET'),
            'response' => $value,
        ]);

        if (! $response->json()['success']) {
            $fail('La verificación del captcha ha fallado. Por favor intenta de nuevo.');
        }
    }
}
