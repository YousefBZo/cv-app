<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageLevelRequest;
use App\Http\Resources\LanguageResource;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends BaseApiController
{
    protected LanguageService $service;

    public function __construct(LanguageService $service)
    {
        $this->service = $service;
    }

    public function available(): JsonResponse
    {
        $languages = $this->service->available();

        return $this->successResponse($languages);
    }

    public function index(Request $request): JsonResponse
    {
        $languages = $this->service->index($request);

        return $this->successResponse(LanguageResource::collection($languages));
    }

    public function store(StoreLanguageRequest $request): JsonResponse
    {
        $languages = $this->service->store($request);

        return $this->createdResponse(LanguageResource::collection($languages));
    }

    public function update(UpdateLanguageLevelRequest $request, int $languageId): JsonResponse
    {
        $languages = $this->service->update($request, $languageId);

        return $this->successResponse(LanguageResource::collection($languages), 'Language level updated successfully');
    }

    public function destroy(Request $request, int $languageId): JsonResponse
    {
        $this->service->destroy($request, $languageId);

        return $this->successResponse(null, 'Language removed successfully');
    }
}
