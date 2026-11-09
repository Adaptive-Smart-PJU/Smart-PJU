<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Pengelola = 'pengelola';
    case Teknisi = 'teknisi';

    public function label(): string
    {
        return match ($this) {
            self::Pengelola => 'Pengelola',
            self::Teknisi => 'Teknisi',
        };
    }
}
