<?php

namespace App\Services;

use App\Http\Requests\StoreCertificationRequest;
use App\Http\Requests\UpdateCertificationRequest;
use App\Models\Certification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificationService
{
    /**
     * OPTIMIZATION #7 — Select only needed columns + order by issue_date.
     */
    public function index(Request $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile->certifications()
            ->select(['id', 'profile_id', 'name', 'organization', 'photo', 'issue_date', 'expiration_date'])
            ->orderByDesc('issue_date')
            ->get();
    }

    public function store(StoreCertificationRequest $request): Certification
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $data['photo'] = $file->store('certifications', 'public');
        }

        $data['profile_id'] = $profile->id;

        return Certification::create($data);
    }

    public function update(UpdateCertificationRequest $request, Certification $certification): Certification
    {
        $profile = $request->user()->profile;

        if (! $profile || $certification->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($certification->photo) {
                Storage::disk('public')->delete($certification->photo);
            }
            $file = $request->file('photo');
            $data['photo'] = $file->store('certifications', 'public');
        }

        $certification->update($data);

        return $certification->fresh();
    }

    public function destroy(Request $request, Certification $certification): void
    {
        $profile = $request->user()->profile;

        if (! $profile || $certification->profile_id !== $profile->id) {
            abort(403, 'Unauthorized');
        }

        if ($certification->photo) {
            Storage::disk('public')->delete($certification->photo);
        }

        $certification->delete();
    }
}
