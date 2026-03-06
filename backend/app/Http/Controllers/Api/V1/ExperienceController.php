<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use App\Services\ExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceController extends BaseApiController
{
    protected ExperienceService $service;

    public function __construct(ExperienceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $experiences = $this->service->index($request);

        return $this->successResponse(ExperienceResource::collection($experiences));
    }

    public function store(StoreExperienceRequest $request): JsonResponse
    {
        $experience = $this->service->store($request);

        return $this->createdResponse(ExperienceResource::make($experience));
    }

    public function update(UpdateExperienceRequest $request, Experience $experience): JsonResponse
    {
        $experience = $this->service->update($request, $experience);

        return $this->successResponse(ExperienceResource::make($experience), 'Experience updated successfully');
    }

    public function destroy(Request $request, Experience $experience): JsonResponse
    {
        $this->service->destroy($request, $experience);

        return $this->successResponse(null, 'Experience deleted successfully');
    }
}
