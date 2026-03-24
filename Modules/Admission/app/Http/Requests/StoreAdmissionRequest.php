<?php

namespace Modules\Admission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public API
    }

    public function rules(): array
    {
        return [
            // ---- Mandatory Fields ----
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],

            // Student info (mandatory)
            'student_name_bn' => ['required', 'string', 'max:255'],
            'student_name_en' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],

            // Student info (optional)
            'birth_registration_no' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:50'],
            'has_disability' => ['nullable', 'boolean'],
            'disability_details' => ['nullable', 'required_if:has_disability,true', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],

            // Photo (optional, with format & size validation)
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB max

            // ---- Father's Info (at least father or mother or guardian required) ----
            'father_name_bn' => ['nullable', 'string', 'max:255'],
            'father_name_en' => ['nullable', 'string', 'max:255'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'father_mobile' => ['nullable', 'string', 'max:20'],
            'father_nid' => ['nullable', 'string', 'max:50'],

            // ---- Mother's Info ----
            'mother_name_bn' => ['nullable', 'string', 'max:255'],
            'mother_name_en' => ['nullable', 'string', 'max:255'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'mother_mobile' => ['nullable', 'string', 'max:20'],
            'mother_nid' => ['nullable', 'string', 'max:50'],

            // ---- Guardian (required only if both father & mother are empty) ----
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relation' => ['nullable', 'string', 'max:100'],
            'guardian_mobile' => ['nullable', 'string', 'max:20'],

            // ---- Present Address (mandatory) ----
            'present_village' => ['required', 'string', 'max:255'],
            'present_post_office' => ['nullable', 'string', 'max:255'],
            'present_thana' => ['required', 'string', 'max:255'],
            'present_district' => ['required', 'string', 'max:255'],
            'present_post_code' => ['nullable', 'string', 'max:10'],

            // ---- Permanent Address ----
            'same_as_present' => ['nullable', 'boolean'],
            'permanent_village' => ['nullable', 'required_if:same_as_present,false', 'string', 'max:255'],
            'permanent_post_office' => ['nullable', 'string', 'max:255'],
            'permanent_thana' => ['nullable', 'required_if:same_as_present,false', 'string', 'max:255'],
            'permanent_district' => ['nullable', 'required_if:same_as_present,false', 'string', 'max:255'],
            'permanent_post_code' => ['nullable', 'string', 'max:10'],

            // ---- Previous School ----
            'previous_school_name' => ['nullable', 'string', 'max:255'],
            'previous_class' => ['nullable', 'string', 'max:50'],
            'previous_section' => ['nullable', 'string', 'max:50'],

            // ---- Special / Quota ----
            'is_freedom_fighter_child' => ['nullable', 'boolean'],
            'quota_details' => ['nullable', 'string', 'max:500'],

            // ---- Signatures (file uploads) ----
            'student_signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'], // 1MB max
            'guardian_signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'], // 1MB max
        ];
    }

    /**
     * Custom validation: at least father, mother, or guardian must be provided.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasFather = !empty($this->father_name_bn) || !empty($this->father_name_en);
            $hasMother = !empty($this->mother_name_bn) || !empty($this->mother_name_en);
            $hasGuardian = !empty($this->guardian_name);

            if (!$hasFather && !$hasMother && !$hasGuardian) {
                $validator->errors()->add(
                    'guardian_name',
                    __('admission::validation.guardian_required')
                );
            }

            // If no father and no mother, guardian fields become mandatory
            if (!$hasFather && !$hasMother) {
                if (empty($this->guardian_name)) {
                    $validator->errors()->add('guardian_name', __('admission::validation.guardian_name_required'));
                }
                if (empty($this->guardian_mobile)) {
                    $validator->errors()->add('guardian_mobile', __('admission::validation.guardian_mobile_required'));
                }
                if (empty($this->guardian_relation)) {
                    $validator->errors()->add('guardian_relation', __('admission::validation.guardian_relation_required'));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'student_name_bn.required' => __('admission::validation.student_name_bn_required'),
            'student_name_en.required' => __('admission::validation.student_name_en_required'),
            'date_of_birth.required' => __('admission::validation.date_of_birth_required'),
            'date_of_birth.before' => __('admission::validation.date_of_birth_before'),
            'gender.required' => __('admission::validation.gender_required'),
            'class_id.required' => __('admission::validation.class_required'),
            'academic_year_id.required' => __('admission::validation.academic_year_required'),
            'photo.image' => __('admission::validation.photo_image'),
            'photo.mimes' => __('admission::validation.photo_mimes'),
            'photo.max' => __('admission::validation.photo_max'),
            'student_signature.image' => __('admission::validation.signature_image'),
            'student_signature.mimes' => __('admission::validation.signature_mimes'),
            'student_signature.max' => __('admission::validation.signature_max'),
            'guardian_signature.image' => __('admission::validation.signature_image'),
            'guardian_signature.mimes' => __('admission::validation.signature_mimes'),
            'guardian_signature.max' => __('admission::validation.signature_max'),
            'present_village.required' => __('admission::validation.present_address_required'),
            'present_thana.required' => __('admission::validation.present_address_required'),
            'present_district.required' => __('admission::validation.present_address_required'),
        ];
    }
}
