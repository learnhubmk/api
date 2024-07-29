<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\StoreContentManagerManagementRequest;
use App\Admin\Http\Requests\UpdateContentManagerManagementRequest;
use App\Admin\Http\Resources\ContentManagerManagementResource;
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

class ContentManagerManagementController
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

        $contentManagers = User::query()
            ->with(['roles', 'contentManagerProfile'])
            ->whereRelation('roles', 'name', RoleName::CONTENT_MANAGER->value)
            ->when($searchQuery, function (EloquentQueryBuilder $query) use ($searchQuery) {
                return $query
                    ->whereRelation('contentManagerProfile', 'first_name', 'LIKE', "$searchQuery%")
                    ->orWhereRelation('contentManagerProfile', 'last_name', 'LIKE', "$searchQuery%");
            })
            ->orderBy(function (Builder $query) use ($sortBy) {
                return $query->from('content_manager_profiles')
                    ->whereColumn('content_manager_profiles.user_id', '=', 'users.id')
                    ->select("content_manager_profiles.$sortBy");
            }, $sortDirection)
            ->paginate($recordsPerPage);

        return ContentManagerManagementResource::collection($contentManagers);
    }

    public function show(int $id): ContentManagerManagementResource
    {
        $user = User::query()->with(['roles', 'contentManagerProfile'])
            ->whereRelation('roles', 'name', RoleName::CONTENT_MANAGER->value)
            ->findOrFail($id);

        return new ContentManagerManagementResource($user);
    }

    /**
     * Store a new resource in storage.
     * @throws \Throwable
     */
    public function store(StoreContentManagerManagementRequest $request): ContentManagerManagementResource
    {
        $contentManager = DB::transaction(function () use ($request) {
            /** @var User $contentManager */
            $contentManager = User::query()->create([
                'email' => $request->get('email'),
                'password' => Str::password(),
                'email_verified_at' => now(),
                'status' => UserStatusName::ACTIVE->value,
            ]);

            $contentManager->contentManagerProfile()->create($request->only(['first_name' , 'last_name']));

            $contentManager->assignRole(RoleName::CONTENT_MANAGER);

            Password::broker()->sendResetLink(['email' => $request->get('email')]);

            return $contentManager;
        });

        return new ContentManagerManagementResource($contentManager);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContentManagerManagementRequest $request, int $id): ContentManagerManagementResource
    {
        /** @var User $contentManager */
        $contentManager = User::query()->with(['roles', 'contentManagerProfile'])
            ->whereRelation('roles', 'name', RoleName::CONTENT_MANAGER->value)
            ->findOrFail($id);

        $image = $request->file('image')?->storePubliclyAs('profile-pictures');

        $contentManager->update(['email' => $request->get('email')]);

        $contentManager->contentManagerProfile()->update([
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'image' => $image ?? $contentManager->contentManagerProfile->image
        ]);

        return new ContentManagerManagementResource($contentManager);
    }

    /**
     * Remove the specified resource from storage.
     * @throws \Throwable
     */
    public function destroy(int $id): Response
    {
        /** @var User $contentManager */
        $contentManager = User::query()
            ->whereRelation('roles', 'name', RoleName::CONTENT_MANAGER->value)
            ->findOrFail($id);

        DB::transaction(function () use ($contentManager) {
            $contentManager->update(['status' => UserStatusName::DELETED]);
            $contentManager->contentManagerProfile()->delete();
            $contentManager->delete();
        });

        return response()->noContent();
    }
}
