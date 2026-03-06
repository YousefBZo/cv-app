<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    protected ProjectService $service;

    public function __construct(ProjectService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $projects = $this->service->index($request);

        return $this->successResponse(ProjectResource::collection($projects));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->service->store($request);

        return $this->createdResponse(ProjectResource::make($project));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project = $this->service->update($request, $project);

        return $this->successResponse(ProjectResource::make($project), 'Project updated successfully');
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->service->destroy($request, $project);

        return $this->successResponse(null, 'Project deleted successfully');
    }
}
