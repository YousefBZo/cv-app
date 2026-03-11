<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProfileView — Records a visit to a user's public profile/CV.
 *
 * DESIGN:
 *   - viewer_id is nullable (anonymous/guest visitors are tracked too).
 *   - ip_address stored ONLY for anonymous views (for de-duplication).
 *   - One view per (viewer OR IP, profile) per day — duplicate prevention at application level.
 *   - No updated_at column — views are write-once.
 */
class ProfileView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'profile_id',
        'viewer_id',
        'ip_address',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    /**
     * The profile that was viewed.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * The authenticated user who viewed (null for guests).
     */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }
}
