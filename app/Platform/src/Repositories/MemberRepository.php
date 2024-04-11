<?php

namespace App\Platform\Repositories;


use App\Platform\Enums\UserTypeEnum;
use App\Platform\Interfaces\MemberRepositoryInterface;
use App\Platform\Models\Member;

class MemberRepository implements MemberRepositoryInterface
{

    public function createMember($request)
    {
        $member = Member::create($request->only('email', 'password'));
        $member->assignRole(UserTypeEnum::Member);
        $member->memberProfile()->create($request->only('first_name', 'last_name', 'website_url', 'linkedIn_url', 'gitHub_url', 'behance_url', 'dribbble_url'));

        return $member;
    }
}
