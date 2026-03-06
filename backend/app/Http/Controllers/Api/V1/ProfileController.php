<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseApiController
{
    protected ProfileService $service;

    public function __construct(ProfileService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $profile = $this->service->index($request);

        return $this->successResponse(ProfileResource::make($profile));
    }

    public function store(StoreProfileRequest $request): JsonResponse
    {
        $profile = $this->service->storeProfile($request);

        return $this->createdResponse(ProfileResource::make($profile));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->service->updateProfile($request);

        return $this->successResponse(ProfileResource::make($profile), 'Profile updated successfully');
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->service->destroyProfile($request);

        return $this->successResponse(null, 'Account deleted successfully');
    }
}
