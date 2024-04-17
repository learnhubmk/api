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
        if (isset($request->skills)) {
            foreach ($request->skills as $skill) {
                $member->skills()->create($skill);
            }
        }
        if (isset($request->learning_interests)) {
            foreach ($request->learning_interests as $learning_interest) {
                $member->learning_interests()->create($learning_interest);
            }
        }


        return $member;
    }
}
