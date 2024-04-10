<?php

namespace App\Platform\Repositories;


use App\Platform\Enums\UserTypeEnum;
use App\Platform\Interfaces\MemberRepositoryInterface;
use App\Platform\Models\Member;

class MemberRepository implements MemberRepositoryInterface
{

    public function createMember(array $data)
    {
        $user = Member::create($data);
        $user->assignRole(UserTypeEnum::Member);
        return $user;
    }
}
