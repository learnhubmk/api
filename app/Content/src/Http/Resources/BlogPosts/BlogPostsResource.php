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
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'tags' => $this->whenLoaded('tags', BlogPostTagResource::collection($this->tags)),
            'publish_date' => $this->publish_date,
            'created_at' => $this->created_at,
            'author' => $this->whenLoaded('author', new BlogAuthorResource($this->author)),
            'image' => $this->resource->image,
        ];
    }
}
