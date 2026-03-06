<?php

namespace App\Services;

use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Models\Education;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EducationService
{
    /**
     * OPTIMIZATION #6 — Cache static fields_of_study.json in memory.
     */
    public function availableFields(): array
    {
        return Cache::rememberForever('available_fields_of_study', function () {
            $path = resource_path('lang/fields_of_study.json');
            return json_decode(file_get_contents($path), true);
        });
    }

    /**
     * OPTIMIZATION #7 — Select only needed columns + order by start_date.
     */
    public function index(Request $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile->educations()
            ->select(['id', 'profile_id', 'institution', 'degree', 'field_of_study', 'start_date', 'end_date', 'description'])
            ->orderByDesc('start_date')
            ->get();
    }

    public function store(StoreEducationRequest $request): Education
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $data = $request->validated();
        $data['profile_id'] = $profile->id;

        return Education::create($data);
    }

    public function update(UpdateEducationRequest $request, Education $education): Education
    {
        $profile = $request->user()->profile;

        if (! $profile || $education->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $education->update($request->validated());

        return $education->fresh();
    }

    public function destroy(Request $request, Education $education): void
    {
        $profile = $request->user()->profile;

        if (! $profile || $education->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $education->delete();
    }
}
