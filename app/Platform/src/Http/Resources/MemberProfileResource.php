<?php

namespace App\Platform\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'website_url' => $this->website_url,
            'linkedIn_url' => $this->linkedIn_url,
            'gitHub_url' => $this->gitHub_url,
            'behance_url' => $this->behance_url,
            'dribbble_url' => $this->dribbble_url,
        ];
    }
}
