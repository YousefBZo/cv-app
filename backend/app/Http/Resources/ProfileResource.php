<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — Removed unused created_at/updated_at from response.
 * Kept updated_at only on profile level for cache validation (ETag).
 */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'photo'      => $this->photo ? asset('storage/' . $this->photo) : null,
            'headline'   => $this->headline,
            'summary'    => $this->summary,
            'location'   => $this->location,

            // Nested relationships (only included when loaded)
            'educations'            => EducationResource::collection($this->whenLoaded('educations')),
            'experiences'           => ExperienceResource::collection($this->whenLoaded('experiences')),
            'skills'                => SkillResource::collection($this->whenLoaded('skills')),
            'projects'              => ProjectResource::collection($this->whenLoaded('projects')),
            'certifications'        => CertificationResource::collection($this->whenLoaded('certifications')),
            'volunteer_experiences' => VolunteerExperienceResource::collection($this->whenLoaded('volunteerExperiences')),
            'languages'             => LanguageResource::collection($this->whenLoaded('languages')),

            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
