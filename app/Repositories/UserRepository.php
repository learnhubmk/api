<?php

namespace App\Repositories;

use App\Enums\UserType;
use App\Enums\UserTypeEnum;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{

    public function createUser(array $data)
    {
        $user = User::create($data);
        $user->assignRole(UserTypeEnum::Member);
        return $user;
    }
}
