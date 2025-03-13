<?php

namespace App\Website\Http\Resources\Tags;

use App\Website\Models\BlogPostTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BlogPostTag
 */
class BlogPostTagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->name,
        ];
    }
}
