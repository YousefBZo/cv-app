<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reaction — A user's reaction on another user's profile.
 *
 * TYPES: like | love | celebrate | insightful | curious
 *   - like        → General approval ("Good CV!")
 *   - love        → Emotional ("Amazing work!")
 *   - celebrate   → Congratulatory ("Impressive achievements!")
 *   - insightful  → Intellectual ("Interesting background!")
 *   - curious     → Engagement ("Want to know more!")
 *
 * DESIGN:
 *   - One reaction per (user, profile) pair — unique constraint at DB level.
 *   - Users can toggle or switch type.
 *   - Reactions are soft-toggled (create/delete), keeping the table clean.
 */
class Reaction extends Model
{
    protected $fillable = [
        'user_id',
        'profile_id',
        'type',
    ];

    /**
     * The user who reacted.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The profile that received the reaction.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
