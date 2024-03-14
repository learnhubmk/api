<?php

namespace App\Website\Http\Resources\Blogs;

use App\Website\Http\Resources\User\AuthorBlogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListBlogsResource extends JsonResource
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
            'tags' => $this->tags,
            'publish_date' => $this->created_at,
            'author' => $this->whenLoaded('user', new AuthorBlogResource($this->user)),
        ];
    }
}
