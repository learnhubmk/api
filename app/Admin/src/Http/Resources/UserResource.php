<?php

namespace App\Admin\Http\Resources;

use App\Framework\Enums\RoleName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = $this->roles->first()->name;

        $profileRelation = match($role) {
            RoleName::MEMBER->value => 'memberProfile',
            RoleName::CONTENT_MANAGER->value => 'contentManagerProfile',
            RoleName::ADMIN->value => 'adminProfile',
        };

        return [
            'id' => $this->id,
            'first_name' => $this->$profileRelation->first_name,
            'last_name' => $this->$profileRelation->last_name,
            'email' => $this->email,
            'role' => $role,
        ];
    }
}
