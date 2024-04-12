<?php

namespace App\Platform\Http\Resources;

use App\Platform\Models\MemberProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        $member = [];
        $member['id'] = $this->id;
        $member['email'] = $this->email;
        $member['memberProfile'] = new MemberProfileResource($this->memberProfile);
        $member['skills'] = SkillsResource::make($this->skills)->all();
        return $member;
    }
}
