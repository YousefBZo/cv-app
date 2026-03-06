<?php

namespace App\Services;

use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ExperienceService
{
    /**
     * OPTIMIZATION #7 — Select only needed columns + order by start_date.
     * Avoids SELECT * which fetches created_at/updated_at unnecessarily.
     */
    public function index(Request $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile->experiences()
            ->select(['id', 'profile_id', 'company', 'position', 'start_date', 'end_date', 'description'])
            ->orderByDesc('start_date')
            ->get();
    }

    public function store(StoreExperienceRequest $request): Experience
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $data = $request->validated();
        $data['profile_id'] = $profile->id;

        return Experience::create($data);
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): Experience
    {
        $profile = $request->user()->profile;

        if (! $profile || $experience->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $experience->update($request->validated());

        return $experience->fresh();
    }

    public function destroy(Request $request, Experience $experience): void
    {
        $profile = $request->user()->profile;

        if (! $profile || $experience->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $experience->delete();
    }
}
