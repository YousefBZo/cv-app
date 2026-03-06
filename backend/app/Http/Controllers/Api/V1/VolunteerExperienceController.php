<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreVolunteerExperienceRequest;
use App\Http\Requests\UpdateVolunteerExperienceRequest;
use App\Http\Resources\VolunteerExperienceResource;
use App\Models\VolunteerExperience;
use App\Services\VolunteerExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VolunteerExperienceController extends BaseApiController
{
    protected VolunteerExperienceService $service;

    public function __construct(VolunteerExperienceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $volunteers = $this->service->index($request);

        return $this->successResponse(VolunteerExperienceResource::collection($volunteers));
    }

    public function store(StoreVolunteerExperienceRequest $request): JsonResponse
    {
        $volunteer = $this->service->store($request);

        return $this->createdResponse(VolunteerExperienceResource::make($volunteer));
    }

    public function update(UpdateVolunteerExperienceRequest $request, VolunteerExperience $volunteer): JsonResponse
    {
        $volunteer = $this->service->update($request, $volunteer);

        return $this->successResponse(VolunteerExperienceResource::make($volunteer), 'Volunteer experience updated successfully');
    }

    public function destroy(Request $request, VolunteerExperience $volunteer): JsonResponse
    {
        $this->service->destroy($request, $volunteer);

        return $this->successResponse(null, 'Volunteer experience deleted successfully');
    }
}
