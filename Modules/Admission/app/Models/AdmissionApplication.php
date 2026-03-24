<?php

namespace Modules\Admission\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Admission\Enums\AdmissionStatus;
use OwenIt\Auditing\Contracts\Auditable;

class AdmissionApplication extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'tracking_code',
        'academic_year_id',
        'class_id',
        'status',
        // Student info
        'student_name_bn',
        'student_name_en',
        'date_of_birth',
        'gender',
        'birth_registration_no',
        'blood_group',
        'nationality',
        'religion',
        'has_disability',
        'disability_details',
        'email',
        'mobile',
        'photo',
        // Father
        'father_name_bn',
        'father_name_en',
        'father_occupation',
        'father_mobile',
        'father_nid',
        // Mother
        'mother_name_bn',
        'mother_name_en',
        'mother_occupation',
        'mother_mobile',
        'mother_nid',
        // Guardian
        'guardian_name',
        'guardian_relation',
        'guardian_mobile',
        // Present address
        'present_village',
        'present_post_office',
        'present_thana',
        'present_district',
        'present_post_code',
        // Permanent address
        'permanent_village',
        'permanent_post_office',
        'permanent_thana',
        'permanent_district',
        'permanent_post_code',
        'same_as_present',
        // Previous school
        'previous_school_name',
        'previous_class',
        'previous_section',
        // Special
        'is_freedom_fighter_child',
        'quota_details',
        // Signatures
        'student_signature',
        'guardian_signature',
        // Review
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'rejection_reason',
        // Audit
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => AdmissionStatus::class,
        'date_of_birth' => 'date',
        'has_disability' => 'boolean',
        'same_as_present' => 'boolean',
        'is_freedom_fighter_child' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    // ---- Relationships ----

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ---- Scopes ----

    public function scopeStatus($query, AdmissionStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', AdmissionStatus::PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', AdmissionStatus::ACCEPTED);
    }

    // ---- Helpers ----

    public function isPending(): bool
    {
        return $this->status === AdmissionStatus::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === AdmissionStatus::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === AdmissionStatus::REJECTED;
    }

    public function canBeReviewed(): bool
    {
        return in_array($this->status, [
            AdmissionStatus::PENDING,
            AdmissionStatus::UNDER_REVIEW,
        ]);
    }

    /**
     * Check if guardian info is required (both father and mother are empty)
     */
    public function needsGuardian(): bool
    {
        return empty($this->father_name_bn) && empty($this->father_name_en)
            && empty($this->mother_name_bn) && empty($this->mother_name_en);
    }
}
