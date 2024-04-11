<?php

namespace App\Platform\Http\Controllers\Auth;

use App\Platform\Http\Controllers\BaseApiController;
use App\Platform\Http\Requests\RegisterUserRequest;
use App\Platform\Http\Resources\MemberResource;
use App\Platform\Interfaces\MemberRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserRegisterController extends BaseApiController
{
    public function __construct(private MemberRepositoryInterface $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterUserRequest $request): Response
    {
        $member = $this->memberRepository->createMember($request);
        event(new Registered($member));

        return $this->response(MemberResource::make($member));
    }
}
