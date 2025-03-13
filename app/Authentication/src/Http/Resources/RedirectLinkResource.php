<?php

namespace App\Authentication\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedirectLinkResource extends JsonResource
{
    protected string $redirectLink;

    public function __construct(string $redirectLink)
    {
        parent::__construct(null);
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
