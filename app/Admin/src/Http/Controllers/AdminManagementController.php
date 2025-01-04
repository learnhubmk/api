<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\RestoreAdminManagementRequest;
use App\Admin\Http\Requests\StoreAdminManagementRequest;
use App\Admin\Http\Requests\UpdateAdminManagementRequest;
use App\Admin\Http\Resources\AdminManagementResource;
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
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Throwable;

class AdminManagementController
{
    /**
     * Display a listing of the resource.
     */
    #[Authenticated]
    #[Endpoint(title: 'Administrators Listing', description: 'This endpoint lists all administrators')]
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

        $administrators = User::query()
            ->with(['roles', 'adminProfile'])
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->when($searchQuery, function (EloquentQueryBuilder $query) use ($searchQuery) {
                return $query
                    ->whereRelation('adminProfile', 'first_name', 'LIKE', "$searchQuery%")
                    ->orWhereRelation('adminProfile', 'last_name', 'LIKE', "$searchQuery%");
            })
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy(function (Builder $query) use ($sortBy) {
                return $query->from('admin_profiles')
                    ->whereColumn('admin_profiles.user_id', '=', 'users.id')
                    ->select("admin_profiles.$sortBy");
            }, $sortDirection)
            ->paginate($recordsPerPage);

        return AdminManagementResource::collection($administrators);
    }


    #[Authenticated]
    #[Endpoint(title: 'Administrator Profile Details', description: 'This endpoint shows details of a specific administrator profile')]
    #[Group('Admin')]
    public function show(int $id): AdminManagementResource
    {
        $admin = User::query()->with(['roles', 'adminProfile'])
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->findOrFail($id);

        return new AdminManagementResource($admin);
    }

    /**
     * Store a new resource in storage.
     * @throws Throwable
     */
    #[Authenticated]
    #[Endpoint(title: 'Administrator Invitation', description: 'This endpoint invites a new administrator to join')]
    #[Group('Admin')]
    #[BodyParam('first_name', 'string', required: true, example: "John")]
    #[BodyParam('last_name', 'string', required: true, example: "Doe")]
    #[BodyParam('email', 'string', required: true, example: "johndoes@learnhub.mk")]
    public function store(StoreAdminManagementRequest $request): AdminManagementResource
    {
        $admin = DB::transaction(function () use ($request) {
            /** @var User $admin */
            $admin = User::query()->create([
                'email' => $request->get('email'),
                'password' => Str::password(),
                'email_verified_at' => now(),
                'status' => UserStatusName::ACTIVE->value,
            ]);

            $admin->adminProfile()->create($request->only(['first_name' , 'last_name']));

            $admin->assignRole(RoleName::ADMIN);

            Author::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_id' => $admin->id,
            ]);

            Password::broker()->sendResetLink(['email' => $request->get('email')]);

            return $admin;
        });

        return new AdminManagementResource($admin);
    }

    /**
     * Update the specified resource in storage.
     * @throws Throwable
     */
    #[Authenticated]
    #[Endpoint(title: 'Edit Administrator Details', description: 'This endpoint edits the details of an administrator profile')]
    #[Group('Admin')]
    #[BodyParam('first_name', 'string', required: true, example: "John")]
    #[BodyParam('last_name', 'string', required: true, example: "Doe")]
    #[BodyParam('email', 'string', required: true, example: "johndoes@learnhub.mk")]
    public function update(UpdateAdminManagementRequest $request, int $id): AdminManagementResource
    {
        $admin = DB::transaction(function () use ($id, $request) {
            /** @var User $admin */
            $admin = User::query()->with(['roles', 'adminProfile'])
                ->whereRelation('roles', 'name', RoleName::ADMIN->value)
                ->findOrFail($id);

            $image = $request->file('image')?->storePubliclyAs('profile-pictures');

            $admin->update(['email' => $request->get('email')]);

            $admin->adminProfile()->update([
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'image' => $image ?? $admin->adminProfile->image
            ]);

            Author::query()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_id' => $admin->id,
                'image' => $image ?? $admin->adminProfile->image
            ]);

            return $admin;
        });

        return new AdminManagementResource($admin->fresh());
    }

    /**
     * Remove the specified resource from storage.
     * @throws Throwable
     */
    #[Authenticated]
    #[Endpoint(title: 'Administrator Profile Deletion', description: 'This endpoint deletes a specific administrator profile')]
    #[Group('Admin')]
    public function destroy(int $id): Response
    {
        /** @var User $admin */
        $admin = User::query()
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->findOrFail($id);

        if ($admin->is(auth()->user())) {
            throw ValidationException::withMessages([
                'message' => __('custom-exception-messages.forbidden_profile_deletion_message')
            ]);
        }

        DB::transaction(function () use ($admin) {
            $admin->update(['status' => UserStatusName::DELETED]);
            $admin->adminProfile()->delete();
            $admin->delete();
        });

        return response()->noContent();
    }


    #[Authenticated]
    #[Endpoint(title: 'Restore Deleted Administrator', description: 'This endpoint restores deleted admin profile')]
    #[Group('Admin')]
    public function restore(RestoreAdminManagementRequest $request, int $id): Response
    {
        /** @var User $admin */
        $admin = User::query()
            ->whereRelation('roles', 'name', RoleName::ADMIN->value)
            ->onlyTrashed()
            ->findOrFail($id);

        $admin->restore();

        return response()->noContent();
    }
}
