<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillLevelRequest;
use App\Http\Resources\SkillResource;
use App\Services\SkillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends BaseApiController
{
    protected SkillService $service;

    public function __construct(SkillService $service)
    {
        $this->service = $service;
    }

    public function available(): JsonResponse
    {
        $skills = $this->service->available();

        return $this->successResponse($skills);
    }

    public function index(Request $request): JsonResponse
    {
        $skills = $this->service->index($request);

        return $this->successResponse(SkillResource::collection($skills));
    }

    public function store(StoreSkillRequest $request): JsonResponse
    {
        $skills = $this->service->store($request);

        return $this->createdResponse(SkillResource::collection($skills));
    }

    public function update(UpdateSkillLevelRequest $request, int $skillId): JsonResponse
    {
        $skills = $this->service->update($request, $skillId);

        return $this->successResponse(SkillResource::collection($skills), 'Skill level updated successfully');
    }

    public function destroy(Request $request, int $skillId): JsonResponse
    {
        $this->service->destroy($request, $skillId);

        return $this->successResponse(null, 'Skill removed successfully');
    }
}
