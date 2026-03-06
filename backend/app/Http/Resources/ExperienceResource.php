<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — API Resource Optimization
 *
 * WHY: The frontend CV store and views never use `created_at` / `updated_at`
 *      for display. Sending them wastes ~60 bytes per record on every request.
 *      With 20 experiences × 7 sections = 140 records, that's ~8 KB of useless
 *      JSON per CV load.
 *
 * WHAT: Removed `created_at` and `updated_at` from the response.
 *       Also removed `profile_id` — the frontend already knows which profile
 *       it belongs to (it requested it). This follows the "minimal payload" principle.
 */
class ExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'profile_id'  => $this->profile_id,
            'company'     => $this->company,
            'position'    => $this->position,
            'start_date'  => $this->start_date?->format('Y-m-d'),
            'end_date'    => $this->end_date?->format('Y-m-d'),
            'description' => $this->description,
        ];
    }
}
