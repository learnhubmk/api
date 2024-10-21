<?php

namespace App\Platform\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'email' => $this->email,
            'is_verified' => $this->hasVerifiedEmail(),
            'role' => $this->roles->first()?->name,
            'profile' => $this->whenLoaded('memberProfile', new MemberProfileDetailsResource($this->memberProfile)),
        ];
    }
}
