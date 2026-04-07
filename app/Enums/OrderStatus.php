<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case READY = 'ready';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
