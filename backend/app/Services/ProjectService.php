<?php

namespace App\Services;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectService
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

        return $profile->projects()
            ->select(['id', 'profile_id', 'title', 'description', 'link', 'github_url', 'cover', 'start_date', 'end_date'])
            ->orderByDesc('start_date')
            ->get();
    }

    public function store(StoreProjectRequest $request): Project
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $data = $request->validated();
        $data['profile_id'] = $profile->id;

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $data['cover'] = $file->store('projects', 'public');
        }

        return Project::create($data);
    }

    public function update(UpdateProjectRequest $request, Project $project): Project
    {
        $profile = $request->user()->profile;

        if (! $profile || $project->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validated();

        if ($request->hasFile('cover')) {
            if ($project->cover) {
                Storage::disk('public')->delete($project->cover);
            }
            $file = $request->file('cover');
            $data['cover'] = $file->store('projects', 'public');
        }

        $project->update($data);

        return $project->fresh();
    }

    public function destroy(Request $request, Project $project): void
    {
        $profile = $request->user()->profile;

        if (! $profile || $project->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        if ($project->cover) {
            Storage::disk('public')->delete($project->cover);
        }

        $project->delete();
    }
}
