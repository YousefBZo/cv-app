<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\ProfileResource;
use App\Services\CVService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CVController extends BaseApiController
{
    protected CVService $service;

    public function __construct(CVService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $profile = $this->service->index();

        if (! $profile) {
            return $this->successResponse(null, 'No profile found');
        }

        return $this->successResponse(ProfileResource::make($profile));
    }
}
