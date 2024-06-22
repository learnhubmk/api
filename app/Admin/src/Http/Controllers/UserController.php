<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Resources\UserResource;
use App\Framework\Enums\RoleName;
use App\Framework\Enums\UserStatusName;
use App\Framework\Models\User;
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
        $data = $request->validate([
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'role' => ['nullable', Rule::enum(RoleName::class)],
            'sortBy' => ['nullable', 'in:id,first_name,last_name'],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ]);

        $role = $request->get('role') ?? RoleName::MEMBER->value;

        $users = User::query()
            ->filter($data, $role)
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
