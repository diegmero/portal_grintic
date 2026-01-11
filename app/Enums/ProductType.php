<?php

namespace App\Enums;

enum ProductType: string
{
    case SUBSCRIPTION = 'subscription';
    case ONE_TIME = 'one_time';

    public function label(): string
    {
        return match($this) {
            self::SUBSCRIPTION => 'Suscripción',
            self::ONE_TIME => 'Compra Única',
        };
    }
}
