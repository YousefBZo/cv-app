<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — Removed unused created_at/updated_at from response.
 */
class VolunteerExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'profile_id'   => $this->profile_id,
            'organization' => $this->organization,
            'role'         => $this->role,
            'start_date'   => $this->start_date?->format('Y-m-d'),
            'end_date'     => $this->end_date?->format('Y-m-d'),
            'description'  => $this->description,
        ];
    }
}
