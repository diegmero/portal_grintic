<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';
    case Partial = 'partial';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Borrador',
            self::Sent => 'Enviada',
            self::Paid => 'Pagada',
            self::Overdue => 'Vencida',
            self::Void => 'Anulada',
            self::Partial => 'Parcial',
            self::Cancelled => 'Cancelada',
        };
    }
}
