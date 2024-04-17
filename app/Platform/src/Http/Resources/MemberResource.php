<?php

namespace App\Platform\Http\Resources;


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
        $member['skills'] = SkillsResource::collection($this->skills);
        $member['learning_interests'] = LearningInterestResource::collection($this->learning_interests);
        $member['experiences'] = ExperienceResource::collection($this->experiences);
        return $member;
    }
}
