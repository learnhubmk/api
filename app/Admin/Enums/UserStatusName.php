<?php

namespace App\Admin\Enums;

enum UserStatusName: string
{
    case ACTIVE = 'active';
    case BANNED = 'banned';
    case DELETED = 'deleted';

}
