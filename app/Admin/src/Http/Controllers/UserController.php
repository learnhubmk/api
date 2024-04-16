<?php

namespace App\Admin\Http\Controllers;

use App\Models\User;
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
        ]);

        return UserResource::collection(
            User::query()
                ->with(['roles'])
                ->filter($validated)
                ->paginate(20)
        );
    }

    // TODO: Check why model binding is not working, ex: User $user
    public function show(int $id)
    {
        $user = User::findOrFail($id);

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
