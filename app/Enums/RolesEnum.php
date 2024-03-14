<?php
namespace App\Enums;

enum RolesEnum: string
{
    case ADMIN = 'Admin';
    case MEMBER = 'Member';


    public function label(): string
    {
        return match ($this) {
            static::ADMIN => 'Admin',
            static::MEMBER => 'Member',
        };


    }

}


