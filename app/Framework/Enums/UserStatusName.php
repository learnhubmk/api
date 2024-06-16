<?php

namespace App\Framework\Enums;

enum UserStatusName: string
{
    case ACTIVE = 'active';
    case BANNED = 'banned';
    case DELETED = 'deleted';

}
