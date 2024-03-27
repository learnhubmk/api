<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{

    public function createUser(array $data)
    {
        $user = User::create($data);
        $user->assignRole(User::MEMBER_ROLE);
        return $user;
    }
}
