<?php

namespace App\Services;

use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageLevelRequest;
use App\Models\Language;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageService
{
    /**
     * OPTIMIZATION #6 — Response Caching for Static Data
     *
     * WHY: languages.json is a static file that never changes at runtime.
     *      Without caching, every call to this endpoint:
     *        1. Opens the file from disk (filesystem I/O)
     *        2. Reads all bytes into memory
     *        3. Decodes JSON string → PHP array
     *      With caching, this happens once, then subsequent calls read
     *      from in-memory cache (0ms vs ~2-5ms per request).
     *
     * HOW: Cache::rememberForever() stores the result permanently in the
     *      configured cache driver (file/redis). It only runs the closure
     *      on the very first call. After that, it returns the cached value.
     */
    public function available(): array
    {
        return Cache::rememberForever('available_languages', function () {
            $path = resource_path('lang/languages.json');
            return json_decode(file_get_contents($path), true);
        });
    }

    public function index(Request $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        return $profile->languages()->get();
    }

    public function store(StoreLanguageRequest $request): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found. Create profile first.');
        }

        $items = $request->validated()['languages'];

        $syncData = [];
        foreach ($items as $item) {
            $language = Language::firstOrCreate(['name' => $item['name']]);
            $syncData[$language->id] = ['level' => $item['level']];
        }

        $profile->languages()->sync($syncData);

        return $profile->languages()->get();
    }

    public function update(UpdateLanguageLevelRequest $request, int $languageId): Collection
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        // Verify the language is attached to this profile
        if (! $profile->languages()->where('language_id', $languageId)->exists()) {
            abort(404, 'Language not found on your profile.');
        }

        $profile->languages()->updateExistingPivot($languageId, [
            'level' => $request->validated()['level'],
        ]);

        return $profile->languages()->get();
    }

    public function destroy(Request $request, int $languageId): void
    {
        $profile = $request->user()->profile;

        if (! $profile) {
            abort(404, 'Profile not found');
        }

        $profile->languages()->detach($languageId);
    }
}
