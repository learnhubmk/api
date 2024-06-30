<?php

namespace App\Admin\Http\Controllers;

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberManagementController
{
    /**
     * Display a listing of the resource.
     */
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

    public function show(int $id): MemberManagementResource
    {
        $member = User::query()->with(['roles', 'memberProfile'])
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->findOrFail($id);

        return new MemberManagementResource($member);
    }

    /**
     * Store a new resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberManagementRequest $request, int $id)
    {
        $member = User::query()->with(['roles', 'memberProfile'])
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->findOrFail($id);

        $image = $request->file('image')?->storePubliclyAs('profile-pictures');

        $member->update(['email' => $request->get('email')]);

        $member->memberProfile()->update(['first_name', 'last_name', 'image' => $image ?? $member->memberProfile->image]);

        return new MemberManagementResource($member);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): Response
    {
        $member = User::query()
            ->whereRelation('roles', 'name', RoleName::MEMBER->value)
            ->findOrFail($id);

        $member->update(['status' => UserStatusName::DELETED]);
        $member->memberProfile->delete();
        $member->delete();

        return response()->noContent();
    }
}
