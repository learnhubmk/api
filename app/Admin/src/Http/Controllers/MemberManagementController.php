<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\StoreMemberManagementRequest;
use App\Admin\Http\Requests\UpdateMemberManagementRequest;
use App\Admin\Http\Resources\MemberManagementResource;
use App\Framework\Enums\RoleName;
use App\Framework\Enums\UserStatusName;
use App\Framework\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;

class MemberManagementController
{
    /**
     * Display a listing of the resource.
     */
    #[Authenticated]
    #[Endpoint(title: 'Members Listing', description: 'This endpoint lists all members')]
    #[Group('Admin')]
    #[QueryParam('query', 'string', required: false, example: "john")]
    #[QueryParam('sort_by', 'string', required: false, example: "first_name")]
    #[QueryParam('sort_direction', 'string', required: false, example: "asc")]
    #[QueryParam('per_page', 'integer', required: false, example: "20")]
    public function index(Request $request): AnonymousResourceCollection
    {
        $searchQuery = $request->query('query');
        $sortBy = $request->query('sort_by');
        $sortBy = in_array($sortBy, ['created_at', 'first_name', 'last_name']) ? $sortBy : 'created_at';
        $sortDirection = $request->query('sort_direction');
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
        $recordsPerPage = min((int) $request->query('per_page') ?? 20, 100);

        $users = User::query()
            ->with(['roles', 'memberProfile'])
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->when($searchQuery, function (EloquentQueryBuilder $query) use ($searchQuery) {
                return $query
                    ->whereRelation('memberProfile', 'first_name', 'LIKE', "$searchQuery%")
                    ->orWhereRelation('memberProfile', 'last_name', 'LIKE', "$searchQuery%");
            })
            ->orderBy(function (Builder $query) use ($sortBy) {
                return $query->from('member_profiles')
                    ->whereColumn('member_profiles.user_id', '=', 'users.id')
                    ->select("member_profiles.$sortBy");
            }, $sortDirection)
            ->paginate($recordsPerPage);

        return MemberManagementResource::collection($users);
    }

    #[Authenticated]
    #[Endpoint(title: 'Member Profile Details', description: 'This endpoint shows details of a specific member profile')]
    #[Group('Admin')]
    public function show(int $id): MemberManagementResource
    {
        $member = User::query()->with(['roles', 'memberProfile'])
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->findOrFail($id);

        return new MemberManagementResource($member);
    }

    /**
     * Store a new resource in storage.
     * @throws \Throwable
     */
    #[Authenticated]
    #[Endpoint(title: 'Member Invitation', description: 'This endpoint invites a new member to join')]
    #[Group('Admin')]
    #[BodyParam('first_name', 'string', required: true, example: "John")]
    #[BodyParam('last_name', 'string', required: true, example: "Doe")]
    #[BodyParam('email', 'string', required: true, example: "johndoes@gmail.com")]
    public function store(StoreMemberManagementRequest $request): MemberManagementResource
    {
        $member = DB::transaction(function () use ($request) {
            /** @var User $member */
            $member = User::query()->create([
                'email' => $request->get('email'),
                'password' => Str::password(),
                'email_verified_at' => now(),
                'status' => UserStatusName::ACTIVE->value,
            ]);

            $member->memberProfile()->create($request->only(['first_name' , 'last_name']));

            $member->assignRole(RoleName::MEMBER);

            Password::broker()->sendResetLink(['email' => $request->get('email')]);

            return $member;
        });

        return new MemberManagementResource($member);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authenticated]
    #[Endpoint(title: 'Edit Member Details', description: 'This endpoint edits the details of a member profile')]
    #[Group('Admin')]
    #[BodyParam('first_name', 'string', required: true, example: "John")]
    #[BodyParam('last_name', 'string', required: true, example: "Doe")]
    #[BodyParam('email', 'string', required: true, example: "johndoes@gmail.com")]
    public function update(UpdateMemberManagementRequest $request, int $id): MemberManagementResource
    {
        /** @var User $member */
        $member = User::query()->with(['roles', 'memberProfile'])
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->findOrFail($id);

        $image = $request->file('image')?->storePubliclyAs('profile-pictures');

        $member->update(['email' => $request->get('email')]);

        $member->memberProfile()->update([
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'image' => $image ?? $member->memberProfile->image
        ]);

        return new MemberManagementResource($member->fresh());
    }

    /**
     * Remove the specified resource from storage.
     * @throws \Throwable
     */
    #[Authenticated]
    #[Endpoint(title: 'Member Profile Deletion', description: 'This endpoint deletes a specific member profile')]
    #[Group('Admin')]
    public function destroy(int $id): Response
    {
        /** @var User $member */
        $member = User::query()
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->findOrFail($id);

        DB::transaction(function () use ($member) {
            $member->update(['status' => UserStatusName::DELETED]);
            $member->memberProfile()->delete();
            $member->delete();
        });

        return response()->noContent();
    }
}
