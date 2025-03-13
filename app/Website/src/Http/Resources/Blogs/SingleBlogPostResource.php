<?php

namespace App\Website\Http\Resources\Blogs;

use App\Website\Http\Resources\Tags\BlogPostTagResource;
use App\Website\Http\Resources\User\BlogAuthorResource;
use App\Website\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BlogPost
 */
class SingleBlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'excerpt' => $this->resource->excerpt,
            'content' => $this->resource->content,
            'tags' => $this->whenLoaded('tags', BlogPostTagResource::collection($this->resource->tags)),
            'publish_date' => $this->resource->publish_date,
            'author' => $this->whenLoaded('author', new BlogAuthorResource($this->resource->author)),
        ];
    }
}

