<?php

namespace App\Services;

use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillLevelRequest;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SkillService
{
    /**
     * OPTIMIZATION #6 — Cache static skills.json file in memory.
     * Same reasoning as LanguageService::available().
     */
    public function available(): array
    {
        return Cache::rememberForever('available_skills', function () {
            $path = resource_path('lang/skills.json');
            return json_decode(file_get_contents($path), true);
        });
    }

    public function index(Request $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile->skills()->get();
    }

    public function store(StoreSkillRequest $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $items = $request->validated()['skills'];

        $syncData = [];
        foreach ($items as $item) {
            $skill = Skill::firstOrCreate(['name' => $item['name']]);
            $syncData[$skill->id] = ['level' => $item['level']];
        }

        $profile->skills()->syncWithoutDetaching($syncData);

        return $profile->skills()->get();
    }

    public function update(UpdateSkillLevelRequest $request, int $skillId): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        // Verify the skill is attached to this profile
        if (! $profile->skills()->where('skill_id', $skillId)->exists()) {
            abort(404, 'Skill not found on your profile.');
        }

        $profile->skills()->updateExistingPivot($skillId, [
            'level' => $request->validated()['level'],
        ]);

        return $profile->skills()->get();
    }

    public function destroy(Request $request, int $skillId): void
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        $profile->skills()->detach($skillId);
    }
}
