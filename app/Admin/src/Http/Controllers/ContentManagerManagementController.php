<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\RestoreContentManagerManagementRequest;
use App\Admin\Http\Requests\StoreContentManagerManagementRequest;
use App\Admin\Http\Requests\UpdateContentManagerManagementRequest;
use App\Admin\Http\Resources\ContentManagerManagementResource;
use App\Admin\Models\Author;
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

class ContentManagerManagementController
{
    /**
     * Display a listing of the resource.
     */
    #[Authenticated]
    #[Endpoint(title: 'Content managers Listing', description: 'This endpoint lists all content managers')]
    #[Group('Admin')]
    #[QueryParam('query', 'string', required: false, example: "john")]
    #[QueryParam('sort_by', 'string', required: false, example: "first_name")]
    #[QueryParam('sort_direction', 'string', required: false, example: "asc")]
    #[QueryParam('per_page', 'integer', required: false, example: "20")]
    #[QueryParam('status', 'string', required: false, example: 'active', enum: ['active', 'deleted', 'banned'])]
    public function index(Request $request): AnonymousResourceCollection
    {
        $searchQuery = $request->query('query');
        $sortBy = $request->query('sort_by');
        $sortBy = in_array($sortBy, ['created_at', 'first_name', 'last_name']) ? $sortBy : 'created_at';
        $sortDirection = $request->query('sort_direction');
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
        $recordsPerPage = min((int) $request->query('per_page') ?? 20, 100);
        $status = $request->query('status');

        $contentManagers = User::query()
            ->with(['roles', 'contentManagerProfile'])
            ->where('status', $status)
            ->whereRelation('roles', 'name', RoleName::CONTENT_MANAGER->value)
            ->when($searchQuery, function (EloquentQueryBuilder $query) use ($searchQuery) {
                return $query
                    ->whereRelation('contentManagerProfile', 'first_name', 'LIKE', "$searchQuery%")
                    ->orWhereRelation('contentManagerProfile', 'last_name', 'LIKE', "$searchQuery%");
            })
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy(function (Builder $query) use ($sortBy) {
                return $query->from('content_manager_profiles')
                    ->whereColumn('content_manager_profiles.user_id', '=', 'users.id')
                    ->select("content_manager_profiles.$sortBy");
            }, $sortDirection)
            ->paginate($recordsPerPage);

        return ContentManagerManagementResource::collection($contentManagers);
    }


    #[Authenticated]
    #[Endpoint(title: 'Content Manager Profile Details', description: 'This endpoint shows details of a specific content manager profile')]
    #[Group('Admin')]
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
    #[Authenticated]
    #[Endpoint(title: 'Content Manager Invitation', description: 'This endpoint invites a new content manager to join')]
    #[Group('Admin')]
    #[BodyParam('first_name', 'string', required: true, example: "John")]
    #[BodyParam('last_name', 'string', required: true, example: "Doe")]
    #[BodyParam('email', 'string', required: true, example: "johndoes@learnhub.mk")]
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

            Author::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_id' => $contentManager->id,
            ]);

            Password::broker()->sendResetLink(['email' => $request->get('email')]);

            return $contentManager;
        });

        return new ContentManagerManagementResource($contentManager->fresh());
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    #[Authenticated]
    #[Endpoint(title: 'Edit Content Manager Details', description: 'This endpoint edits the details of a content manager profile')]
    #[Group('Admin')]
    #[BodyParam('first_name', 'string', required: true, example: "John")]
    #[BodyParam('last_name', 'string', required: true, example: "Doe")]
    #[BodyParam('email', 'string', required: true, example: "johndoes@learnhub.mk")]
    public function update(UpdateContentManagerManagementRequest $request, int $id): ContentManagerManagementResource
    {
        $contentManager = DB::transaction(function () use ($id, $request) {
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

            Author::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_id' => $contentManager->id,
                'image' => $image ?? $contentManager->contentManagerProfile->image
            ]);

            return $contentManager;
        });

        return new ContentManagerManagementResource($contentManager);
    }

    /**
     * Remove the specified resource from storage.
     * @throws \Throwable
     */

    #[Authenticated]
    #[Endpoint(title: 'Content Manager Profile Deletion', description: 'This endpoint deletes a specific content manager profile')]
    #[Group('Admin')]
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

    #[Authenticated]
    #[Endpoint(title: 'Restore Deleted Content Manager', description: 'This endpoint restores deleted content manager profile')]
    #[Group('Admin')]
    public function restore(RestoreContentManagerManagementRequest $request, int $id): Response
    {
        /** @var User $contentManager */
        $contentManager = User::query()
            ->whereRelation('roles', 'name', RoleName::CONTENT_MANAGER->value)
            ->onlyTrashed()
            ->findOrFail($id);

        $contentManager->restore();

        return response()->noContent();
    }
}
