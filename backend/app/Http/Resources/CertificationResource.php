<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — Removed unused created_at/updated_at from response.
 */
class CertificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'profile_id'      => $this->profile_id,
            'name'            => $this->name,
            'organization'    => $this->organization,
            'photo'           => $this->photo ? asset('storage/' . $this->photo) : null,
            'issue_date'      => $this->issue_date?->format('Y-m-d'),
            'expiration_date' => $this->expiration_date?->format('Y-m-d'),
        ];
    }
}
