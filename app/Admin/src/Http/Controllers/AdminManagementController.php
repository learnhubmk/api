<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Resources\AdminManagementResource;
use App\Framework\Enums\RoleName;
use App\Framework\Enums\UserStatusName;
use App\Framework\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AdminManagementController
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
            ->with(['roles', 'adminProfile'])
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->when($searchQuery, function (EloquentQueryBuilder $query) use ($searchQuery) {
                return $query
                    ->whereRelation('adminProfile', 'first_name', 'LIKE', "$searchQuery%")
                    ->orWhereRelation('adminProfile', 'last_name', 'LIKE', "$searchQuery%");
            })
            ->orderBy(function (Builder $query) use ($sortBy) {
                return $query->from('admin_profiles')
                    ->whereColumn('admin_profiles.user_id', '=', 'users.id')
                    ->select("admin_profiles.$sortBy");
            }, $sortDirection)
            ->paginate($recordsPerPage);

        return AdminManagementResource::collection($users);
    }

    public function show(int $id): AdminManagementResource
    {
        $user = User::query()->with(['roles', 'adminProfile'])
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->findOrFail($id);

        return new AdminManagementResource($user);
    }

    /**
     * Store a new resource in storage.
     */
    public function store(Request $request, string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): Response
    {
        $user = User::query()
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->findOrFail($id);

        $user->update(['status' => UserStatusName::DELETED]);
        $user->adminProfile->delete();
        $user->delete();

        return response()->noContent();
    }
}
