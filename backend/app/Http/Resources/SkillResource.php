<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — Removed unused created_at/updated_at from response.
 */
class SkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'level' => $this->whenPivotLoaded('profile_skill', fn () => $this->pivot->level),
        ];
    }
}
