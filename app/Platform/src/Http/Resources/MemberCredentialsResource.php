<?php

namespace App\Platform\Http\Resources;

use App\Framework\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class MemberCredentialsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'email' => $this->resource->email,
            'is_verified' => $this->resource->hasVerifiedEmail(),
            'role' => $this->resource->roles->first()?->name,
            'profile' => $this->whenLoaded('memberProfile', fn () => new MemberProfileDetailsResource($this->resource->memberProfile)),
        ];
    }
}
