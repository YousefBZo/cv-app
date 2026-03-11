<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OPTIMIZATION #5 — Removed unused timestamps from UserResource.
 * The frontend auth store only uses id, name, email.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'email'   => $this->email,
            'slug'    => $this->slug,
            'profile' => ProfileResource::make($this->whenLoaded('profile')),
        ];
    }
}
