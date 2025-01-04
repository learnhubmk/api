<?php

namespace App\Website\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogAuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'image' => $this->image,
        ];
    }
}
