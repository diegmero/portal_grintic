<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case OnHold = 'on_hold';
    case Archived = 'archived';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Activo',
            self::Completed => 'Completado',
            self::OnHold => 'En Pausa',
            self::Archived => 'Archivado',
            self::Cancelled => 'Cancelado',
        };
    }
}
