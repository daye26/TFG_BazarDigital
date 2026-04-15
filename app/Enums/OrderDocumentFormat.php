<?php

namespace App\Enums;

enum OrderDocumentFormat: string
{
    case TICKET = 'ticket';

    public function label(): string
    {
        return match ($this) {
            self::TICKET => 'Ticket',
        };
    }

    public function filePrefix(): string
    {
        return match ($this) {
            self::TICKET => 'ticket',
        };
    }
}
