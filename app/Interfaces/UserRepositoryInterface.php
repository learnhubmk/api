<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function createUser(array $attributes);
}
