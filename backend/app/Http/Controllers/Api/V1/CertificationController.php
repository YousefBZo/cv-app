<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreCertificationRequest;
use App\Http\Requests\UpdateCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use App\Services\CertificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificationController extends BaseApiController
{
    protected CertificationService $service;

    public function __construct(CertificationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $certifications = $this->service->index($request);

        return $this->successResponse(CertificationResource::collection($certifications));
    }

    public function store(StoreCertificationRequest $request): JsonResponse
    {
        $certification = $this->service->store($request);

        return $this->createdResponse(CertificationResource::make($certification));
    }

    public function update(UpdateCertificationRequest $request, Certification $certification): JsonResponse
    {
        $certification = $this->service->update($request, $certification);

        return $this->successResponse(CertificationResource::make($certification), 'Certification updated successfully');
    }

    public function destroy(Request $request, Certification $certification): JsonResponse
    {
        $this->service->destroy($request, $certification);

        return $this->successResponse(null, 'Certification deleted successfully');
    }
}
