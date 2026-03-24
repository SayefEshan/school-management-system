<?php

namespace Modules\Admission\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Admission\Http\Requests\StoreAdmissionRequest;
use Modules\Admission\Services\AdmissionService;
use Modules\Admission\Transformers\AdmissionApplicationResource;

class AdmissionApiController extends Controller
{
    /**
     * Submit a new admission application (public)
     * POST /api/v1/admissions
     */
    public function store(StoreAdmissionRequest $request): JsonResponse
    {
        try {
            $application = AdmissionService::submit($request->validated());

            return apiResponse(
                true,
                __('admission::messages.application_submitted'),
                [
                    'tracking_code' => $application->tracking_code,
                    'application' => new AdmissionApplicationResource($application->load(['academicYear', 'classModel'])),
                ],
                code: 201
            );
        } catch (Exception $e) {
            Log::error('Admission submission failed: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $request->except(['photo', 'student_signature', 'guardian_signature']),
            ]);
            return apiResponse(false, 'Something went wrong. Please try again.', code: 500);
        }
    }

    /**
     * Check application status by tracking code (public)
     * GET /api/v1/admissions/{tracking_code}/status
     */
    public function status(string $trackingCode): JsonResponse
    {
        try {
            $application = AdmissionService::findByTrackingCode($trackingCode);

            if (!$application) {
                return apiResponse(false, __('admission::messages.application_not_found'), code: 404);
            }

            return apiResponse(true, 'Application Status', [
                'tracking_code' => $application->tracking_code,
                'status' => $application->status->value,
                'status_label' => $application->status->label(),
                'status_label_bn' => $application->status->labelBn(),
                'submitted_at' => $application->created_at->toDateTimeString(),
                'reviewed_at' => $application->reviewed_at?->toDateTimeString(),
            ]);
        } catch (Exception $e) {
            Log::error('Admission status check failed: ' . $e->getMessage(), [
                'tracking_code' => $trackingCode,
                'exception' => $e,
            ]);
            return apiResponse(false, 'Something went wrong. Please try again.', code: 500);
        }
    }
}
