<?php

namespace App\Platform\Enums;

enum RoleName: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';

    public function label(): string
    {
        return match ($this) {

            self::ADMIN => ucfirst(self::ADMIN->value),
            self::MEMBER => ucfirst(self::MEMBER->value),
        };

    }
}
