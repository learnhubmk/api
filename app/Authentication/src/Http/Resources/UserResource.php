<?php

namespace App\Authentication\Http\Resources;

use App\Framework\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_verified' => $this->hasVerifiedEmail(),
            'email' => $this->email,
            'role' => $this->roles->first()?->name,
        ];
    }
}

