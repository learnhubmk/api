<?php

namespace App\Admin\Http\Controllers;

use App\Enums\RoleName;
use App\Admin\Http\Resources\UserResource;
use App\Enums\UserStatusName;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'role' => ['nullable', Rule::enum(RoleName::class)],
            'sortBy' => ['nullable', 'in:id,first_name,last_name'],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ]);

        $role = $request->get('role', RoleName::MEMBER->value);

        $sortBy = match(true) {
            $role === RoleName::MEMBER->value && $request->get('first_name') => 'member_profiles.first_name',
            $role === RoleName::MEMBER->value && $request->get('last_name') => 'member_profiles.last_name',
            $role === RoleName::ADMIN->value && $request->get('first_name') => 'admin_profiles.first_name',
            $role === RoleName::ADMIN->value && $request->get('last_name') => 'admin_profiles.last_name',
            $role === RoleName::CONTENT_MANAGER->value && $request->get('first_name') => 'content_manager_profiles.first_name',
            $role === RoleName::CONTENT_MANAGER->value && $request->get('last_name') => 'content_manager_profiles.last_name',
            default => 'users.id',
        };

        $joinTable = match ($role) {
            RoleName::MEMBER->value => 'member_profiles',
            RoleName::ADMIN->value => 'admin_profiles',
            RoleName::CONTENT_MANAGER->value => 'content_manager_profiles',
        };

        $users = User::query()
            ->with(['roles'])
            ->join($joinTable, 'users.id', '=', "$joinTable.user_id")
            ->orderBy($sortBy, $validated['sortDirection'] ?? 'asc')
            ->filter($validated)
            ->paginate(20);

        return UserResource::collection($users);
    }

    public function show(int $id): UserResource
    {
        $user = User::query()->with(['roles'])->findOrFail($id);

        $profileRelation = match($user->roles->first()->name) {
            RoleName::MEMBER->value => 'memberProfile',
            RoleName::CONTENT_MANAGER->value => 'contentManagerProfile',
            RoleName::ADMIN->value => 'adminProfile',
            default => null,
        };

        $user->load([$profileRelation]);

        return new UserResource($user);
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
        $user = User::findOrFail($id);

        $user->update(['status' => UserStatusName::DELETED]);

        $user->delete();

        return response()->noContent();
    }
}
