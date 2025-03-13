<?php

namespace App\Content\Http\Resources\BlogPosts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostsResource extends JsonResource
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
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,
            'status' => $this->resource->status,
            'tags' => $this->whenLoaded('tags', BlogPostTagResource::collection($this->resource->tags)),
            'publish_date' => $this->resource->publish_date,
            'created_at' => $this->resource->created_at,
            'author' => $this->whenLoaded('author', new BlogAuthorResource($this->resource->author)),
        ];
    }
}

