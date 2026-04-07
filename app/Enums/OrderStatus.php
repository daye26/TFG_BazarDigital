<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case READY = 'ready';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::READY => 'Listo para recoger',
            self::COMPLETED => 'Entregado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
