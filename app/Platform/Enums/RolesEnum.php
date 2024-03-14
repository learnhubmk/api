<?php

namespace App\Platform\Enums;

enum RolesEnum: string
{
    case ADMIN = 'Admin';
    case MEMBER = 'Member';

    public function label(): string
    {
        return match ($this) {

            self::ADMIN => ucfirst(self::ADMIN->value),
            self::MEMBER => ucfirst(self::MEMBER->value),
        };

    }
}
