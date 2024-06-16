<?php

namespace App\Framework\Enums;
enum RoleName: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case CONTENT_MANAGER = 'content_manager';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => ucfirst(self::ADMIN->value),
            self::MEMBER => ucfirst(self::MEMBER->value),
            self::CONTENT_MANAGER => ucfirst(self::CONTENT_MANAGER->value),
        };

    }
}
