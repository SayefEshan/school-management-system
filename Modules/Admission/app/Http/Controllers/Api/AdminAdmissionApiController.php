<?php

namespace Modules\Admission\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Admission\Enums\AdmissionStatus;
use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Services\AdmissionService;
use Modules\Admission\Transformers\AdmissionApplicationResource;

class AdminAdmissionApiController extends Controller
{
    /**
     * List all admission applications (admin)
     * GET /api/v1/admin/admissions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AdmissionApplication::with(['academicYear', 'classModel', 'reviewer'])
                ->latest();

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by class
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Filter by academic year
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            // Search by name or tracking code
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                        ->orWhere('student_name_en', 'like', "%{$search}%")
                        ->orWhere('student_name_bn', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            }

            $perPage = min($request->integer('per_page', 25), 100);
            $applications = $query->paginate($perPage);

            return apiResponse(true, 'Admission Applications', [
                'applications' => AdmissionApplicationResource::collection($applications),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to list admission applications: ' . $e->getMessage(), ['exception' => $e]);
            return apiResponse(false, 'Something went wrong. Please try again.', code: 500);
        }
    }

    /**
     * View a single application (admin)
     * GET /api/v1/admin/admissions/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $application = AdmissionApplication::with(['academicYear', 'classModel', 'reviewer'])
                ->findOrFail($id);

            return apiResponse(true, 'Application Details', new AdmissionApplicationResource($application));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return apiResponse(false, 'Application not found.', code: 404);
        } catch (Exception $e) {
            Log::error('Failed to show admission application: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return apiResponse(false, 'Something went wrong. Please try again.', code: 500);
        }
    }

    /**
     * Accept an application (admin)
     * POST /api/v1/admin/admissions/{id}/accept
     */
    public function accept(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);

            $application = AdmissionApplication::findOrFail($id);
            $application = AdmissionService::accept($application, $request->input('notes'));

            return apiResponse(true, __('admission::messages.application_accepted'), new AdmissionApplicationResource($application->load(['academicYear', 'classModel', 'reviewer'])));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return apiResponse(false, 'Application not found.', code: 404);
        } catch (Exception $e) {
            Log::error('Failed to accept admission application: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return apiResponse(false, $e->getMessage(), code: 422);
        }
    }

    /**
     * Reject an application (admin)
     * POST /api/v1/admin/admissions/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'reason' => ['required', 'string', 'max:1000'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);

            $application = AdmissionApplication::findOrFail($id);
            $application = AdmissionService::reject($application, $request->reason, $request->notes);

            return apiResponse(true, __('admission::messages.application_rejected'), new AdmissionApplicationResource($application->load(['academicYear', 'classModel', 'reviewer'])));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return apiResponse(false, 'Application not found.', code: 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Let Laravel handle validation exceptions normally
        } catch (Exception $e) {
            Log::error('Failed to reject admission application: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return apiResponse(false, $e->getMessage(), code: 422);
        }
    }

    /**
     * Mark as under review (admin)
     * POST /api/v1/admin/admissions/{id}/review
     */
    public function markUnderReview(int $id): JsonResponse
    {
        try {
            $application = AdmissionApplication::findOrFail($id);
            $application = AdmissionService::markUnderReview($application);

            return apiResponse(true, 'Application marked as under review.', new AdmissionApplicationResource($application->load(['academicYear', 'classModel', 'reviewer'])));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return apiResponse(false, 'Application not found.', code: 404);
        } catch (Exception $e) {
            Log::error('Failed to mark admission as under review: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return apiResponse(false, $e->getMessage(), code: 422);
        }
    }
}
