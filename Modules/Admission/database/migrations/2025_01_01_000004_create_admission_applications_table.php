<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique()->comment('ADM-YYYY-NNNNNN');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('class_id')->constrained('classes');

            // Application status
            $table->enum('status', [
                'pending', 'under_review', 'accepted', 'rejected', 'cancelled'
            ])->default('pending');

            // ---- Student Information ----
            $table->string('student_name_bn')->comment('শিক্ষার্থীর নাম বাংলায়');
            $table->string('student_name_en')->comment('Student Name English');
            $table->date('date_of_birth')->comment('জন্ম তারিখ');
            $table->enum('gender', ['male', 'female', 'other'])->comment('লিঙ্গ');
            $table->string('birth_registration_no')->nullable()->comment('জন্ম নিবন্ধন নং');
            $table->string('blood_group')->nullable()->comment('রক্তের গ্রুপ');
            $table->string('nationality')->default('Bangladeshi')->comment('জাতীয়তা');
            $table->string('religion')->nullable()->comment('ধর্ম');
            $table->boolean('has_disability')->default(false)->comment('প্রতিবন্ধী');
            $table->string('disability_details')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable()->comment('মোবাইল');
            $table->string('photo')->nullable()->comment('ছবি - file path');

            // ---- Father's Information ----
            $table->string('father_name_bn')->nullable()->comment('পিতার নাম বাংলায়');
            $table->string('father_name_en')->nullable()->comment('Father Name English');
            $table->string('father_occupation')->nullable()->comment('পিতার পেশা');
            $table->string('father_mobile')->nullable()->comment('পিতার মোবাইল');
            $table->string('father_nid')->nullable()->comment('পিতার NID');

            // ---- Mother's Information ----
            $table->string('mother_name_bn')->nullable()->comment('মাতার নাম বাংলায়');
            $table->string('mother_name_en')->nullable()->comment('Mother Name English');
            $table->string('mother_occupation')->nullable()->comment('মাতার পেশা');
            $table->string('mother_mobile')->nullable()->comment('মাতার মোবাইল');
            $table->string('mother_nid')->nullable()->comment('মাতার NID');

            // ---- Guardian Information (only if father & mother both empty) ----
            $table->string('guardian_name')->nullable()->comment('অভিভাবকের নাম');
            $table->string('guardian_relation')->nullable()->comment('সম্পর্ক');
            $table->string('guardian_mobile')->nullable()->comment('অভিভাবকের মোবাইল');

            // ---- Present Address (বর্তমান ঠিকানা) ----
            $table->string('present_village')->nullable()->comment('গ্রাম/বাসা');
            $table->string('present_post_office')->nullable()->comment('ডাকঘর');
            $table->string('present_thana')->nullable()->comment('থানা/উপজেলা');
            $table->string('present_district')->nullable()->comment('জেলা');
            $table->string('present_post_code')->nullable()->comment('পোস্ট কোড');

            // ---- Permanent Address (স্থায়ী ঠিকানা) ----
            $table->string('permanent_village')->nullable()->comment('গ্রাম/বাসা');
            $table->string('permanent_post_office')->nullable()->comment('ডাকঘর');
            $table->string('permanent_thana')->nullable()->comment('থানা/উপজেলা');
            $table->string('permanent_district')->nullable()->comment('জেলা');
            $table->string('permanent_post_code')->nullable()->comment('পোস্ট কোড');
            $table->boolean('same_as_present')->default(false)->comment('স্থায়ী ঠিকানা বর্তমান ঠিকানার মতো');

            // ---- Previous School (পূর্ব বিদ্যালয়) ----
            $table->string('previous_school_name')->nullable()->comment('পূর্ব বিদ্যালয়ের নাম');
            $table->string('previous_class')->nullable()->comment('শ্রেণী');
            $table->string('previous_section')->nullable()->comment('বিভাগ');

            // ---- Special / Quota ----
            $table->boolean('is_freedom_fighter_child')->default(false)->comment('মুক্তিযোদ্ধা সন্তান/নাতি');
            $table->text('quota_details')->nullable()->comment('কুটা প্রাপ্তের বিবরণী');

            // ---- Signatures ----
            $table->string('student_signature')->nullable()->comment('ছাত্র/ছাত্রীর স্বাক্ষর - file path');
            $table->string('guardian_signature')->nullable()->comment('অভিভাবকের স্বাক্ষর - file path');

            // ---- Admin / Review ----
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // ---- Audit ----
            tableDataInfo($table);
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('academic_year_id');
            $table->index('class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
