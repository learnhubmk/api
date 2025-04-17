<?php

namespace App\Website\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'tags' => $this->whenLoaded('tags', BlogPostTagResource::collection($this->tags)),
            'publish_date' => $this->publish_date,
            'author' => $this->whenLoaded('author', new BlogAuthorResource($this->author)),
            'image' => $this->resource->image,
        ];
    }
}
