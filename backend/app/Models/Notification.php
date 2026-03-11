<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification — In-app notification for a user.
 *
 * TYPES:
 *   'reaction'     — Someone liked/loved your profile.
 *   'profile_view' — Someone viewed your CV.
 *
 * The `data` JSON column holds contextual info so the frontend can
 * render rich notification messages without extra API calls:
 *   reaction     → { reactor_name, reactor_slug, reaction_type, profile_id }
 *   profile_view → { viewer_name, viewer_slug, profile_id }
 */
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * The user who owns this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the notification has been read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
