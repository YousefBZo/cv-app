<?php

namespace App\Services;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function index(Request $request): Profile
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile;
    }

    public function storeProfile(StoreProfileRequest $request): Profile
    {
        $user = $request->user();

        if ($user->profile) {
            abort(409, 'Profile already exists');
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $data['photo'] = $file->store('photos', 'public');
        }

        $data['user_id'] = $user->id;

        return Profile::create($data);
    }

    public function updateProfile(UpdateProfileRequest $request): Profile
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $file = $request->file('photo');
            $data['photo'] = $file->store('photos', 'public');
        }

        $profile->update($data);

        return $profile->fresh();
    }

    public function destroyProfile(Request $request): void
    {
        $user = $request->user();
        $profile = $user->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        // Delete photo file
        if ($profile->photo) {
            Storage::disk('public')->delete($profile->photo);
        }

        // Delete all related data
        $profile->educations()->delete();
        $profile->experiences()->delete();
        $profile->volunteerExperiences()->delete();
        $profile->projects()->each(function ($project) {
            if ($project->cover) {
                Storage::disk('public')->delete($project->cover);
            }
            $project->delete();
        });
        $profile->certifications()->each(function ($cert) {
            if ($cert->photo) {
                Storage::disk('public')->delete($cert->photo);
            }
            $cert->delete();
        });
        $profile->skills()->detach();
        $profile->languages()->detach();

        $profile->delete();

        // Delete the user account
        $user->tokens()->delete();
        $user->delete();
    }
}
