<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * NotificationResource — Formats notification data for the API response.
 *
 * Provides all the data the frontend needs to render rich notification items:
 *   - Type + contextual data (reactor name, viewer name, etc.)
 *   - Read status
 *   - Human-friendly timestamps
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'data'       => $this->data,
            'read_at'    => $this->read_at?->toIso8601String(),
            'is_read'    => $this->read_at !== null,
            'created_at' => $this->created_at->toIso8601String(),
            'time_ago'   => $this->created_at->diffForHumans(),
        ];
    }
}
