<?php

namespace App\Authentication\Http\Controllers;

use App\Framework\Models\User;
use Illuminate\Routing\Controller;
use App\Authentication\Http\Resources\AuthResource;
use App\Authentication\Http\Requests\MemberLoginRequest;
use App\Authentication\Http\Requests\MemberLogoutRequest;

class MemberAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['index', 'logout']);
        $this->middleware('treblle');
        $this->middleware('stateful');
    }
    public function index():AuthResource
    {
        return new AuthResource(auth()->user());
    }

    public function login(MemberLoginRequest $request): AuthResource
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate($user);

        return new AuthResource($user);
    }

    public function logout(MemberLogoutRequest $request)
    {

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged Out!'
        ]);
    }
}
