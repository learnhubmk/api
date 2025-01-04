<?php

namespace App\Content\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostTagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($tags): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
