<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — Removed unused created_at/updated_at from response.
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'profile_id'  => $this->profile_id,
            'title'       => $this->title,
            'description' => $this->description,
            'link'        => $this->link,
            'github_url'  => $this->github_url,
            'cover'       => $this->cover ? asset('storage/' . $this->cover) : null,
            'start_date'  => $this->start_date?->format('Y-m-d'),
            'end_date'    => $this->end_date?->format('Y-m-d'),
        ];
    }
}
