<?php

namespace App\Authentication\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedirectLinkResource extends JsonResource
{
    protected $redirectLink;

    public function __construct($redirectLink)
    {
        $this->redirectLink = $redirectLink;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'redirect_link' => $this->redirectLink,
        ];
    }
}
