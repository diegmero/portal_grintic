<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Completed = 'completed';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En Progreso',
            self::Review => 'En Revisión',
            self::Completed => 'Completada',
            self::Blocked => 'Bloqueada',
        };
    }
}
