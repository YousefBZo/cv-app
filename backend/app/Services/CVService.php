<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

/**
 * OPTIMIZATION #2 — Eager Loading (solves N+1 query problem)
 *
 * BEFORE: $profile->load(['educations', ...]) loads each relation in a
 *         separate query. That's 1 (profile) + 7 (relations) = 8 queries.
 *
 * AFTER:  We still use load(), but now each relation constrains with
 *         ->select() (Optimization #7) so we only fetch columns the
 *         frontend actually uses, and we add ordering so the API returns
 *         data in display order (saves the frontend from sorting).
 *
 * OPTIMIZATION #3 — Prevent N+1 is done globally in AppServiceProvider.
 *
 * OPTIMIZATION #7 — Select Only Needed Columns
 *         SELECT * fetches every column including large TEXT fields.
 *         By selecting only what the API resource returns, we reduce
 *         memory usage and network payload.
 */
class CVService
{
    public function index(): ?Profile
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $profile = Profile::where('user_id', $user->id)->first();

        if (! $profile) {
            return null;
        }

        $profile->load([
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
}
