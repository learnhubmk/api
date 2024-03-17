<?php

namespace App\Website\Http\Resources\Blogs;

use App\Website\Http\Resources\Tags\BlogPostTagResource;
use App\Website\Http\Resources\User\BlogAuthorResource;
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
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'tags' => $this->whenLoaded('tags', BlogPostTagResource::collection($this->tags)),
            'publish_date' => $this->publish_date ,
            'author' => $this->whenLoaded('author', new BlogAuthorResource($this->author)),
        ];
    }
}
