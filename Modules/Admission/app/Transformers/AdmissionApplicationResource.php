<?php

namespace Modules\Admission\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tracking_code' => $this->tracking_code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_label_bn' => $this->status->labelBn(),

            // Academic
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'name_bn' => $this->academicYear->name_bn,
            ]),
            'class' => $this->whenLoaded('classModel', fn () => [
                'id' => $this->classModel->id,
                'name' => $this->classModel->name,
                'name_bn' => $this->classModel->name_bn,
                'numeric_code' => $this->classModel->numeric_code,
            ]),

            // Student
            'student_name_bn' => $this->student_name_bn,
            'student_name_en' => $this->student_name_en,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'birth_registration_no' => $this->birth_registration_no,
            'blood_group' => $this->blood_group,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'has_disability' => $this->has_disability,
            'disability_details' => $this->disability_details,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'photo' => $this->photo ? asset('storage/' . $this->photo) : null,

            // Father
            'father_name_bn' => $this->father_name_bn,
            'father_name_en' => $this->father_name_en,
            'father_occupation' => $this->father_occupation,
            'father_mobile' => $this->father_mobile,
            'father_nid' => $this->father_nid,

            // Mother
            'mother_name_bn' => $this->mother_name_bn,
            'mother_name_en' => $this->mother_name_en,
            'mother_occupation' => $this->mother_occupation,
            'mother_mobile' => $this->mother_mobile,
            'mother_nid' => $this->mother_nid,

            // Guardian
            'guardian_name' => $this->guardian_name,
            'guardian_relation' => $this->guardian_relation,
            'guardian_mobile' => $this->guardian_mobile,

            // Present Address
            'present_village' => $this->present_village,
            'present_post_office' => $this->present_post_office,
            'present_thana' => $this->present_thana,
            'present_district' => $this->present_district,
            'present_post_code' => $this->present_post_code,

            // Permanent Address
            'permanent_village' => $this->permanent_village,
            'permanent_post_office' => $this->permanent_post_office,
            'permanent_thana' => $this->permanent_thana,
            'permanent_district' => $this->permanent_district,
            'permanent_post_code' => $this->permanent_post_code,
            'same_as_present' => $this->same_as_present,

            // Previous School
            'previous_school_name' => $this->previous_school_name,
            'previous_class' => $this->previous_class,
            'previous_section' => $this->previous_section,

            // Special
            'is_freedom_fighter_child' => $this->is_freedom_fighter_child,
            'quota_details' => $this->quota_details,

            // Signatures
            'student_signature' => $this->student_signature ? asset('storage/' . $this->student_signature) : null,
            'guardian_signature' => $this->guardian_signature ? asset('storage/' . $this->guardian_signature) : null,

            // Review
            'reviewed_by' => $this->whenLoaded('reviewer', fn () => [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
            'reviewed_at' => $this->reviewed_at?->toDateTimeString(),
            'review_notes' => $this->review_notes,
            'rejection_reason' => $this->rejection_reason,

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
