<?php

namespace App\Platform\Http\Resources;

use App\Platform\Models\MemberProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MemberProfile */
class MemberProfileDetailsResource extends JsonResource
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
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'image' => $this->resource->image,
            'user_id' => $this->resource->user_id,
        ];
    }
}
