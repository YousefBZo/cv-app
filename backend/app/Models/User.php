<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Reactions this user has given to other profiles.
     */
    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Notifications for this user (reactions, views, etc.).
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderByDesc('created_at');
    }

    /**
     * Profile views made by this user.
     */
    public function profileViews()
    {
        return $this->hasMany(ProfileView::class, 'viewer_id');
    }

    /**
     * Generate a unique slug from a name.
     *
     * WHY: Slugs must be unique across the users table.
     * If "yousef-bzo" is taken, tries "yousef-bzo-2", "yousef-bzo-3", etc.
     *
     * @param string   $name       The user's display name
     * @param int|null $excludeId  Exclude this user's ID (for updates)
     */
    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'user';
        $slug = $baseSlug;
        $counter = 2;

        while (
            static::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
