<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case STORE = 'store';
    case ONLINE = 'online';
}
