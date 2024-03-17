<?php

namespace App\Website\Http\Resources\Blogs;

use App\Http\Resources\Tags\TagResource;
use App\Website\Http\Resources\User\AuthorBlogResource;
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
            'tags' => $this->whenLoaded('tags', TagResource::collection($this->tags)),
            'publish_date' => $this->created_at,
            'author' => $this->whenLoaded('author', new AuthorBlogResource($this->author)),
        ];
    }
}
