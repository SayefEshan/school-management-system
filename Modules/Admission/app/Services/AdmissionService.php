<?php

namespace Modules\Admission\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Admission\Enums\AdmissionStatus;
use Modules\Admission\Models\AdmissionApplication;

class AdmissionService
{
    /**
     * Submit a new admission application (public API)
     */
    public static function submit(array $data): AdmissionApplication
    {
        return DB::transaction(function () use ($data) {
            $data['tracking_code'] = TrackingCodeService::generate();
            $data['status'] = AdmissionStatus::PENDING;

            // Handle file uploads
            if (isset($data['photo']) && $data['photo']) {
                $data['photo'] = $data['photo']->store('admissions/photos', 'public');
            }
            if (isset($data['student_signature']) && $data['student_signature']) {
                $data['student_signature'] = $data['student_signature']->store('admissions/signatures', 'public');
            }
            if (isset($data['guardian_signature']) && $data['guardian_signature']) {
                $data['guardian_signature'] = $data['guardian_signature']->store('admissions/signatures', 'public');
            }

            // Copy present address to permanent if same_as_present
            if (!empty($data['same_as_present'])) {
                $data['permanent_village'] = $data['present_village'] ?? null;
                $data['permanent_post_office'] = $data['present_post_office'] ?? null;
                $data['permanent_thana'] = $data['present_thana'] ?? null;
                $data['permanent_district'] = $data['present_district'] ?? null;
                $data['permanent_post_code'] = $data['present_post_code'] ?? null;
            }

            return AdmissionApplication::create($data);
        });
    }

    /**
     * Accept an admission application (admin)
     */
    public static function accept(AdmissionApplication $application, ?string $notes = null): AdmissionApplication
    {
        if (!$application->canBeReviewed()) {
            throw new \Exception('This application cannot be reviewed in its current status.');
        }

        $application->update([
            'status' => AdmissionStatus::ACCEPTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // TODO: Dispatch AdmissionAccepted event to trigger student creation
        // event(new \Modules\Admission\Events\AdmissionAccepted($application));

        return $application->fresh();
    }

    /**
     * Reject an admission application (admin)
     */
    public static function reject(AdmissionApplication $application, string $reason, ?string $notes = null): AdmissionApplication
    {
        if (!$application->canBeReviewed()) {
            throw new \Exception('This application cannot be reviewed in its current status.');
        }

        $application->update([
            'status' => AdmissionStatus::REJECTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'review_notes' => $notes,
        ]);

        return $application->fresh();
    }

    /**
     * Mark application as under review (admin)
     */
    public static function markUnderReview(AdmissionApplication $application): AdmissionApplication
    {
        if ($application->status !== AdmissionStatus::PENDING) {
            throw new \Exception('Only pending applications can be marked as under review.');
        }

        $application->update([
            'status' => AdmissionStatus::UNDER_REVIEW,
            'reviewed_by' => Auth::id(),
        ]);

        return $application->fresh();
    }

    /**
     * Get application by tracking code (public lookup)
     */
    public static function findByTrackingCode(string $trackingCode): ?AdmissionApplication
    {
        return AdmissionApplication::where('tracking_code', $trackingCode)->first();
    }
}
