<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Models\User;
use App\Platform\Enums\UserStatusName;
use Illuminate\Http\Request;
use App\Admin\Http\Resources\UserResource;
use App\Platform\Enums\RoleName;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'role' => ['nullable', Rule::enum(RoleName::class)],
            'sortBy' => ['nullable', 'in:id,first_name,last_name'],
            'sortDirection' => ['nullable', 'in:asc,desc'],
        ]);

        $sortFieldMap = [
            'id' => 'users.id',
            'first_name' => 'profiles.first_name',
            'last_name' => 'profiles.last_name',
        ][$validated['sortBy'] ?? 'id'];

        return UserResource::collection(
            User::query()
                ->with(['roles'])
                ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                ->orderBy($sortFieldMap, $validated['sortDirection'] ?? 'asc')
                ->filter($validated)
                ->select([
                    'users.id as id',
                    'users.email as email',
                    'profiles.first_name as first_name',
                    'profiles.last_name as last_name',
                ])
                ->paginate(20)
        );
    }

    public function show(int $id)
    {
        $user = User::query()
            ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('users.id', $id)
            ->select([
                'users.id as id',
                'users.email as email',
                'profiles.first_name as first_name',
                'profiles.last_name as last_name',
            ])
            ->first();

        abort_unless($user, 404);

        return new UserResource($user);
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
