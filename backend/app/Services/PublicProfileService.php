<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * PublicProfileService — Handles public (unauthenticated) profile browsing.
 *
 * This service powers the homepage "Explore Profiles" feature, allowing
 * anyone (recruiters, visitors) to browse all profiles and view individual CVs.
 *
 * DESIGN DECISIONS:
 *   1. Only profiles with a headline are considered "complete" and shown publicly.
 *   2. Pagination (12 per page) to avoid loading all profiles at once.
 *   3. Case-insensitive search using LOWER() — works on both MySQL and PostgreSQL.
 *   4. No explicit select() — withCount() needs SELECT * to generate aggregate aliases.
 *      The PublicProfileCardResource controls which fields are exposed in the API response.
 *   5. No sensitive data exposed (PublicProfileCardResource omits phone, contact_email).
 *   6. Sorting by multiple columns (newest, oldest, name_asc, name_desc, most_skills, most_experience).
 *   7. Location filter — exact match on a specific location.
 *
 * WHY LOWER() instead of ILIKE?
 *   - ILIKE is PostgreSQL-only. LOWER(col) LIKE LOWER(val) works on
 *     MySQL, PostgreSQL, and SQLite — making the app database-agnostic.
 */
class PublicProfileService
{
    /**
     * Whitelist of allowed sort options.
     *
     * WHY a whitelist?
     *   - Prevents SQL injection via the sort_by parameter.
     *   - The frontend sends a key like "newest"; we map it to actual SQL.
     *   - Any value not in this list falls back to "newest".
     */
    private const ALLOWED_SORTS = [
        'newest',
        'oldest',
        'name_asc',
        'name_desc',
        'most_skills',
        'most_experience',
    ];

    /**
     * Get a paginated list of public profiles for the homepage directory.
     * Only includes profiles that have at least a headline (considered "complete").
     *
     * @param string|null $search         Case-insensitive search term (matches name, headline, location, summary)
     * @param int         $perPage        Number of profiles per page
     * @param string      $sortBy         Sort key — one of ALLOWED_SORTS
     * @param string|null $location       Filter by exact location (case-insensitive)
     * @param int|null    $excludeUserId  Exclude this user's profile from results (hide own profile)
     */
    public function getProfiles(
        ?string $search = null,
        int     $perPage = 12,
        string  $sortBy = 'newest',
        ?string $location = null,
        ?int    $excludeUserId = null,
    ): LengthAwarePaginator {
        $query = Profile::query()
            ->whereNotNull('headline')
            ->where('headline', '!=', '')
            ->with(['user:id,name,slug'])
            ->withCount(['skills', 'experiences', 'projects', 'educations', 'reactions', 'views']);

        // ── Exclude the authenticated user's own profile ─────────
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        // ── Case-insensitive search ──────────────────────────────
        // Uses LOWER(column) LIKE '%term%' — works on all DB engines.
        if ($search) {
            $term = '%' . mb_strtolower(trim($search)) . '%';

            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(headline) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(location) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(summary) LIKE ?', [$term])
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->whereRaw('LOWER(name) LIKE ?', [$term]);
                  });
            });
        }

        // ── Location filter ──────────────────────────────────────
        // Exact match (case-insensitive) on a specific city/country.
        if ($location) {
            $query->whereRaw('LOWER(location) = ?', [mb_strtolower(trim($location))]);
        }

        // ── Sorting ──────────────────────────────────────────────
        $this->applySorting($query, $sortBy);

        return $query->paginate($perPage);
    }

    /**
     * Apply sorting to the query based on the sort key.
     *
     * HOW IT WORKS:
     *   1. Validate sortBy against the whitelist (prevent SQL injection).
     *   2. Map each sort key to the actual ORDER BY clause.
     *   3. For relation-based sorts (name, skills, experience),
     *      we use subqueries so the main query stays clean.
     */
    private function applySorting($query, string $sortBy): void
    {
        // Fallback to 'newest' if the sort key isn't in our whitelist
        if (! in_array($sortBy, self::ALLOWED_SORTS, true)) {
            $sortBy = 'newest';
        }

        match ($sortBy) {
            'newest'          => $query->orderByDesc('updated_at'),
            'oldest'          => $query->orderBy('created_at'),
            'name_asc'        => $query->orderBy(
                                    // Subquery: get the user's name for sorting
                                    // This avoids a JOIN, keeping the main select clean
                                    \App\Models\User::select('name')
                                        ->whereColumn('users.id', 'profiles.user_id')
                                        ->limit(1)
                                 ),
            'name_desc'       => $query->orderByDesc(
                                    \App\Models\User::select('name')
                                        ->whereColumn('users.id', 'profiles.user_id')
                                        ->limit(1)
                                 ),
            'most_skills'     => $query->orderByDesc('skills_count'),
            'most_experience' => $query->orderByDesc('experiences_count'),
        };
    }

    /**
     * Get a single profile with all CV sections for public viewing.
     * This is the "view someone's CV" endpoint.
     *
     * @param string $slug The user's slug whose CV to fetch
     */
    public function getPublicCV(string $slug): ?Profile
    {
        $profile = Profile::whereHas('user', fn ($q) => $q->where('slug', $slug))
            ->whereNotNull('headline')
            ->where('headline', '!=', '')
            ->first();

        if (! $profile) {
            return null;
        }

        $profile->load([
            'user:id,name,slug',

            'educations' => fn ($q) => $q
                ->select(['id', 'profile_id', 'institution', 'degree', 'field_of_study', 'start_date', 'end_date', 'description'])
                ->orderByDesc('start_date'),

            'experiences' => fn ($q) => $q
                ->select(['id', 'profile_id', 'company', 'position', 'start_date', 'end_date', 'description'])
                ->orderByDesc('start_date'),

            'volunteerExperiences' => fn ($q) => $q
                ->select(['id', 'profile_id', 'organization', 'role', 'start_date', 'end_date', 'description'])
                ->orderByDesc('start_date'),

            'skills',

            'projects' => fn ($q) => $q
                ->select(['id', 'profile_id', 'title', 'description', 'link', 'github_url', 'cover', 'start_date', 'end_date'])
                ->orderByDesc('start_date'),

            'certifications' => fn ($q) => $q
                ->select(['id', 'profile_id', 'name', 'organization', 'photo', 'issue_date', 'expiration_date'])
                ->orderByDesc('issue_date'),

            'languages',
        ]);

        return $profile;
    }

    /**
     * Get all unique locations from public profiles.
     *
     * WHY a dedicated method?
     *   - The frontend needs a dropdown of available locations for filtering.
     *   - We only return locations from "complete" profiles (have a headline).
     *   - Results are sorted alphabetically for a clean dropdown UX.
     *
     * @return array<string> Sorted list of unique location strings
     */
    public function getAvailableLocations(): array
    {
        return Profile::query()
            ->whereNotNull('headline')
            ->where('headline', '!=', '')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();
    }
}
