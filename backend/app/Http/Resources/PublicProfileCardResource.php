<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PublicProfileCardResource — Lightweight resource for the profile directory listing.
 *
 * WHY a separate resource?
 *   - ProfileResource includes all nested CV sections (educations, experiences, etc.)
 *   - For the directory listing, we only need a "card" view: photo, name, headline,
 *     location, and counts of sections (to show badges like "5 skills", "3 projects").
 *   - This reduces payload from ~5KB per profile to ~300 bytes.
 *
 * SECURITY:
 *   - No phone, contact_email, linkedin, github in listing (only visible on full CV).
 *   - user_id exposed so the frontend can link to /cv/:userId.
 */
class PublicProfileCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'user_slug'        => $this->user?->slug,
            'user_name'        => $this->user?->name,
            'photo'            => $this->photo ? asset('storage/' . $this->photo) : null,
            'headline'         => $this->headline,
            'summary'          => $this->summary ? \Illuminate\Support\Str::limit($this->summary, 120) : null,
            'location'         => $this->location,
            'skills_count'     => $this->skills_count ?? 0,
            'experiences_count'=> $this->experiences_count ?? 0,
            'projects_count'   => $this->projects_count ?? 0,
            'educations_count' => $this->educations_count ?? 0,
            'reactions_count'  => $this->reactions_count ?? 0,
            'views_count'      => $this->views_count ?? 0,
        ];
    }
}
