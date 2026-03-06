<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Http\Resources\EducationResource;
use App\Models\Education;
use App\Services\EducationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends BaseApiController
{
    protected EducationService $service;

    public function __construct(EducationService $service)
    {
        $this->service = $service;
    }

    public function availableFields(): JsonResponse
    {
        $fields = $this->service->availableFields();

        return $this->successResponse($fields);
    }

    public function index(Request $request): JsonResponse
    {
        $educations = $this->service->index($request);

        return $this->successResponse(EducationResource::collection($educations));
    }

    public function store(StoreEducationRequest $request): JsonResponse
    {
        $education = $this->service->store($request);

        return $this->createdResponse(EducationResource::make($education));
    }

    public function update(UpdateEducationRequest $request, Education $education): JsonResponse
    {
        $education = $this->service->update($request, $education);

        return $this->successResponse(EducationResource::make($education), 'Education updated successfully');
    }

    public function destroy(Request $request, Education $education): JsonResponse
    {
        $this->service->destroy($request, $education);

        return $this->successResponse(null, 'Education deleted successfully');
    }
}
