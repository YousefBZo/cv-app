<?php

namespace App\Services;

use App\Http\Requests\StoreVolunteerExperienceRequest;
use App\Http\Requests\UpdateVolunteerExperienceRequest;
use App\Models\VolunteerExperience;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class VolunteerExperienceService
{
    /**
     * OPTIMIZATION #7 — Select only needed columns + order by start_date.
     */
    public function index(Request $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile->volunteerExperiences()
            ->select(['id', 'profile_id', 'organization', 'role', 'start_date', 'end_date', 'description'])
            ->orderByDesc('start_date')
            ->get();
    }

    public function store(StoreVolunteerExperienceRequest $request): VolunteerExperience
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $data = $request->validated();
        $data['profile_id'] = $profile->id;

        return VolunteerExperience::create($data);
    }

    public function update(UpdateVolunteerExperienceRequest $request, VolunteerExperience $volunteer): VolunteerExperience
    {
        $profile = $request->user()->profile;

        if (! $profile || $volunteer->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $volunteer->update($request->validated());

        return $volunteer->fresh();
    }

    public function destroy(Request $request, VolunteerExperience $volunteer): void
    {
        $profile = $request->user()->profile;

        if (! $profile || $volunteer->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $volunteer->delete();
    }
}
