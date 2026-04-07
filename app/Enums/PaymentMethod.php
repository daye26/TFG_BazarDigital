<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case STORE = 'store';
    case ONLINE = 'online';

    public function label(): string
    {
        return match ($this) {
            self::STORE => 'En tienda',
            self::ONLINE => 'Online',
        };
    }
}
