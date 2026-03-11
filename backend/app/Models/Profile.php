<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'photo',
        'headline',
        'summary',
        'location',
        'phone',
        'contact_email',
        'website',
        'linkedin',
        'github',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'profile_skill')
            ->withPivot('level')
            ->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function volunteerExperiences(): HasMany
    {
        return $this->hasMany(VolunteerExperience::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'profile_language')
            ->withPivot('level')
            ->withTimestamps();
    }

    /**
     * Reactions received on this profile (likes/loves from other users).
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Views this profile has received.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ProfileView::class);
    }
}
