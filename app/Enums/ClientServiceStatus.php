<?php

namespace App\Enums;

enum ClientServiceStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Activo',
            self::EXPIRED => 'Vencido',
            self::CANCELLED => 'Cancelado',
            self::SUSPENDED => 'Suspendido',
        };
    }
}
