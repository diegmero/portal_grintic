<?php

namespace App\Enums;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case ANNUAL = 'annual';
    case LIFETIME = 'lifetime';

    public function label(): string
    {
        return match($this) {
            self::MONTHLY => 'Mensual',
            self::ANNUAL => 'Anual',
            self::LIFETIME => 'Pago Único',
        };
    }
}
