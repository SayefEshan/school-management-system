# Student Module - Complete Specification
## Student, Guardian, and Enrollment Management

**Version:** 2.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

The Student module manages the complete student lifecycle from admission acceptance to graduation, including student profiles, guardian relationships, and academic enrollments.

**Note:** This specification follows the **Approve-First, Pay-Later** workflow where students are created upon application approval, and fees are initialized as pending (awaiting payment).

---

## Module Structure

```
Modules/Student/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── StudentController.php
│   │   │   ├── GuardianController.php
│   │   │   ├── EnrollmentController.php
│   │   │   └── Api/
│   │   │       └── StudentApiController.php
│   │   └── Requests/
│   │       ├── StoreStudentRequest.php
│   │       ├── UpdateStudentRequest.php
│   │       ├── StoreGuardianRequest.php
│   │       └── EnrollStudentRequest.php
│   ├── Models/
│   │   ├── Student.php
│   │   ├── Guardian.php
│   │   ├── StudentGuardian.php (pivot)
│   │   └── Enrollment.php
│   ├── Services/
│   │   ├── StudentService.php
│   │   ├── GuardianService.php
│   │   ├── EnrollmentService.php
│   │   └── StudentIDService.php
│   ├── Events/
│   │   ├── StudentCreated.php
│   │   ├── StudentEnrolled.php
│   │   └── StudentPromoted.php
│   ├── Observers/
│   │   └── StudentObserver.php
│   └── Enums/
│       ├── StudentStatus.php
│       ├── EnrollmentStatus.php
│       └── GuardianRelation.php
├── database/
│   ├── migrations/
│   │   ├── create_students_table.php
│   │   ├── create_guardians_table.php
│   │   ├── create_student_guardian_table.php
│   │   └── create_enrollments_table.php
│   └── seeders/
│       └── StudentPermissionSeeder.php
├── resources/
│   └── views/
│       ├── students/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── guardians/
│       └── enrollments/
└── routes/
    ├── web.php
    └── api.php
```

---

## Database Schema

### students Table

```sql
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(9) UNIQUE NOT NULL, -- Numeric: 2506000001

    -- Personal Information
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    birth_registration_no VARCHAR(17) UNIQUE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    religion ENUM('islam', 'hinduism', 'buddhism', 'christianity', 'other') NOT NULL,
    ethnicity VARCHAR(100) DEFAULT 'bengali',
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NULL,

    -- Contact Information
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,

    -- Address
    present_address_bn TEXT NOT NULL,
    present_address_en TEXT NOT NULL,
    permanent_address_bn TEXT NOT NULL,
    permanent_address_en TEXT NOT NULL,

    -- Guardian (Primary)
    primary_guardian_id BIGINT UNSIGNED NULL,

    -- Documents
    photo_path VARCHAR(255) NULL,
    birth_certificate_path VARCHAR(255) NULL,

    -- Admission Details
    admission_year INT UNSIGNED NOT NULL,
    admission_class_id BIGINT UNSIGNED NOT NULL,
    admission_application_id BIGINT UNSIGNED NULL, -- Link to original application

    -- Status
    status ENUM('active', 'withdrawn', 'graduated', 'transferred', 'suspended') DEFAULT 'active',
    withdrawal_date DATE NULL,
    withdrawal_reason TEXT NULL,

    -- Migration
    old_student_id VARCHAR(50) NULL,
    migrated_from_old_system BOOLEAN DEFAULT FALSE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE INDEX idx_student_id (student_id),
    UNIQUE INDEX idx_birth_registration (birth_registration_no),
    INDEX idx_name_en (name_en),
    INDEX idx_status (status),
    INDEX idx_admission_year_class (admission_year, admission_class_id),
    INDEX idx_search (name_en, name_bn, student_id),

    -- Foreign Keys
    FOREIGN KEY (admission_class_id) REFERENCES classes(id),
    FOREIGN KEY (primary_guardian_id) REFERENCES guardians(id) ON DELETE SET NULL,
    FOREIGN KEY (admission_application_id) REFERENCES admission_applications(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

### guardians Table

```sql
CREATE TABLE guardians (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Personal Information
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255) NOT NULL,
    nid VARCHAR(17) NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,

    -- Address
    address_bn TEXT NULL,
    address_en TEXT NULL,

    -- Professional
    occupation VARCHAR(255) NULL,
    workplace VARCHAR(255) NULL,
    yearly_income VARCHAR(100) NULL,

    -- Photo
    photo_path VARCHAR(255) NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_name (name_en, name_bn),
    INDEX idx_phone (phone),
    INDEX idx_nid (nid)
) ENGINE=InnoDB;
```

### student_guardian Table (Pivot)

```sql
CREATE TABLE student_guardian (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    guardian_id BIGINT UNSIGNED NOT NULL,
    relation ENUM('father', 'mother', 'legal_guardian', 'uncle', 'aunt',
                  'grandfather', 'grandmother', 'brother', 'sister', 'other') NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    is_emergency_contact BOOLEAN DEFAULT FALSE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE INDEX idx_student_guardian_unique (student_id, guardian_id),
    INDEX idx_student (student_id),
    INDEX idx_guardian (guardian_id),
    INDEX idx_primary (student_id, is_primary),

    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (guardian_id) REFERENCES guardians(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### enrollments Table

```sql
CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Student & Academic Info
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NULL,

    -- Roll Number
    roll_no INT UNSIGNED NULL,

    -- Status
    status ENUM('active', 'promoted', 'detained', 'withdrawn') DEFAULT 'active',

    -- Dates
    enrollment_date DATE NOT NULL,
    promotion_date DATE NULL,
    withdrawal_date DATE NULL,

    -- Remarks
    remarks TEXT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE INDEX idx_student_year (student_id, academic_year_id),
    INDEX idx_class_section (class_id, section_id),
    INDEX idx_status (status),
    INDEX idx_academic_year (academic_year_id),

    -- Foreign Keys
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

---

## Models

### Student Model

```php
<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Student extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'student_id', 'name_en', 'name_bn', 'date_of_birth', 'birth_registration_no',
        'gender', 'religion', 'ethnicity', 'blood_group',
        'phone', 'email',
        'present_address_bn', 'present_address_en',
        'permanent_address_bn', 'permanent_address_en',
        'primary_guardian_id',
        'photo_path', 'birth_certificate_path',
        'admission_year', 'admission_class_id', 'admission_application_id',
        'status', 'withdrawal_date', 'withdrawal_reason',
        'old_student_id', 'migrated_from_old_system'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'withdrawal_date' => 'date',
        'migrated_from_old_system' => 'boolean',
    ];

    // Relationships
    public function primaryGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'primary_guardian_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot(['relation', 'is_primary', 'is_emergency_contact'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)
            ->where('status', 'active')
            ->latest();
    }

    public function admissionClass(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'admission_class_id');
    }

    public function admissionApplication(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByClass($query, $classId)
    {
        return $query->whereHas('currentEnrollment', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        });
    }

    public function scopeBySection($query, $sectionId)
    {
        return $query->whereHas('currentEnrollment', function ($q) use ($sectionId) {
            $q->where('section_id', $sectionId);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('student_id', 'LIKE', "%{$term}%")
              ->orWhere('name_en', 'LIKE', "%{$term}%")
              ->orWhere('name_bn', 'LIKE', "%{$term}%")
              ->orWhere('birth_registration_no', 'LIKE', "%{$term}%");
        });
    }

    // Accessors
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? FileManagerService::getFile($this->photo_path, 'public')
            : asset('images/default-student.png');
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }
}
```

### Guardian Model

```php
<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    protected $fillable = [
        'name_en', 'name_bn', 'nid', 'phone', 'email',
        'address_bn', 'address_en',
        'occupation', 'workplace', 'yearly_income',
        'photo_path'
    ];

    // Relationships
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot(['relation', 'is_primary', 'is_emergency_contact'])
            ->withTimestamps();
    }

    // Accessors
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? FileManagerService::getFile($this->photo_path, 'public')
            : asset('images/default-guardian.png');
    }
}
```

### Enrollment Model

```php
<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id', 'academic_year_id', 'class_id', 'section_id',
        'roll_no', 'status',
        'enrollment_date', 'promotion_date', 'withdrawal_date',
        'remarks'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'promotion_date' => 'date',
        'withdrawal_date' => 'date',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForAcademicYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }
}
```

---

## Services

### StudentService

```php
<?php

namespace Modules\Student\Services;

use DB;
use Exception;
use Modules\Student\Models\Student;
use Modules\Admission\Models\AdmissionApplication;

class StudentService
{
    /**
     * Create student from accepted admission application
     */
    public static function createFromAdmission(AdmissionApplication $application): Student
    {
        try {
            DB::beginTransaction();

            // Generate student ID
            $studentID = StudentIDService::generateStudentID(
                $application->academicYear->year,
                $application->applying_class_id
            );

            // Create student
            $student = Student::create([
                'student_id' => $studentID,
                'name_en' => $application->student_name_en,
                'name_bn' => $application->student_name_bn,
                'date_of_birth' => $application->date_of_birth,
                'birth_registration_no' => $application->birth_registration_no,
                'gender' => $application->gender,
                'religion' => $application->religion,
                'ethnicity' => $application->ethnicity,
                'phone' => $application->primary_phone,
                'email' => $application->email,
                'present_address_bn' => self::formatAddress($application, 'present', 'bn'),
                'present_address_en' => self::formatAddress($application, 'present', 'en'),
                'permanent_address_bn' => self::formatAddress($application, 'permanent', 'bn'),
                'permanent_address_en' => self::formatAddress($application, 'permanent', 'en'),
                'photo_path' => $application->photo_path,
                'birth_certificate_path' => $application->birth_certificate_path,
                'admission_year' => $application->academicYear->year,
                'admission_class_id' => $application->applying_class_id,
                'admission_application_id' => $application->id,
                'status' => 'active',
            ]);

            // Create guardians
            $guardians = GuardianService::createFromAdmission($application, $student);

            // Set primary guardian
            if ($guardians['primary']) {
                $student->update(['primary_guardian_id' => $guardians['primary']->id]);
            }

            // Create enrollment
            $enrollment = EnrollmentService::createEnrollment(
                $student->id,
                $application->academic_year_id,
                $application->applying_class_id,
                $application->applying_section_id ?? null
            );

            // Initialize student fees (pending payment)
            FeeService::assignFeesToStudent($student, $application->academicYear);

            DB::commit();

            return $student->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Format address from application data
     */
    private static function formatAddress(AdmissionApplication $app, string $type, string $lang): string
    {
        $prefix = $type === 'present' ? 'present_' : 'permanent_';

        return implode(', ', array_filter([
            $app->{$prefix . 'village'},
            $app->{$prefix . 'post_office'},
            $app->{$prefix . 'police_station'},
            $app->{$prefix . 'upazila'},
            $app->{$prefix . 'district'}
        ]));
    }
}
```

### GuardianService

```php
<?php

namespace Modules\Student\Services;

use Modules\Student\Models\Guardian;
use Modules\Student\Models\Student;
use Modules\Admission\Models\AdmissionApplication;

class GuardianService
{
    /**
     * Create guardians from admission application
     */
    public static function createFromAdmission(
        AdmissionApplication $application,
        Student $student
    ): array {
        $guardians = ['primary' => null, 'all' => []];

        // Father
        if ($application->father_name_en) {
            $father = Guardian::create([
                'name_en' => $application->father_name_en,
                'name_bn' => $application->father_name_bn,
                'nid' => $application->father_nid,
                'phone' => $application->father_phone,
                'occupation' => $application->father_occupation,
            ]);

            $student->guardians()->attach($father->id, [
                'relation' => 'father',
                'is_primary' => ($application->who_guardian === 'whoguard1'),
                'is_emergency_contact' => true,
            ]);

            $guardians['all'][] = $father;
            if ($application->who_guardian === 'whoguard1') {
                $guardians['primary'] = $father;
            }
        }

        // Mother
        if ($application->mother_name_en) {
            $mother = Guardian::create([
                'name_en' => $application->mother_name_en,
                'name_bn' => $application->mother_name_bn,
                'nid' => $application->mother_nid,
                'phone' => $application->mother_phone,
                'occupation' => $application->mother_occupation,
            ]);

            $student->guardians()->attach($mother->id, [
                'relation' => 'mother',
                'is_primary' => ($application->who_guardian === 'whoguard2'),
                'is_emergency_contact' => true,
            ]);

            $guardians['all'][] = $mother;
            if ($application->who_guardian === 'whoguard2') {
                $guardians['primary'] = $mother;
            }
        }

        // Other Guardian
        if ($application->who_guardian === 'whoguard3' && $application->guardian_name_en) {
            $guardian = Guardian::create([
                'name_en' => $application->guardian_name_en,
                'name_bn' => $application->guardian_name_bn,
                'nid' => $application->guardian_nid,
                'phone' => $application->guardian_phone,
                'occupation' => $application->guardian_occupation,
                'yearly_income' => $application->guardian_yearly_income,
            ]);

            $student->guardians()->attach($guardian->id, [
                'relation' => 'legal_guardian',
                'is_primary' => true,
                'is_emergency_contact' => true,
            ]);

            $guardians['all'][] = $guardian;
            $guardians['primary'] = $guardian;
        }

        return $guardians;
    }
}
```

### EnrollmentService

```php
<?php

namespace Modules\Student\Services;

use Modules\Student\Models\Enrollment;

class EnrollmentService
{
    /**
     * Create enrollment for student
     */
    public static function createEnrollment(
        int $studentId,
        int $academicYearId,
        int $classId,
        ?int $sectionId = null
    ): Enrollment {
        // Generate roll number
        $rollNo = self::generateRollNumber($classId, $sectionId);

        return Enrollment::create([
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'roll_no' => $rollNo,
            'status' => 'active',
            'enrollment_date' => now(),
        ]);
    }

    /**
     * Generate roll number (serial per class)
     */
    private static function generateRollNumber(int $classId, ?int $sectionId): int
    {
        // Get max roll number for this class (ignoring section)
        $maxRoll = Enrollment::where('class_id', $classId)
            ->where('status', 'active')
            ->max('roll_no');

        return ($maxRoll ?? 0) + 1;
    }

    /**
     * Assign section to student
     */
    public static function assignSection(int $enrollmentId, int $sectionId): void
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $enrollment->update(['section_id' => $sectionId]);
    }

    /**
     * Promote students to next class
     */
    public static function promoteStudents(array $studentIds, int $nextClassId, int $nextYearId): void
    {
        DB::transaction(function () use ($studentIds, $nextClassId, $nextYearId) {
            foreach ($studentIds as $studentId) {
                // Mark current enrollment as promoted
                Enrollment::where('student_id', $studentId)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'promoted',
                        'promotion_date' => now()
                    ]);

                // Create new enrollment
                self::createEnrollment($studentId, $nextYearId, $nextClassId);
            }
        });
    }
}
```

---

## Routes

### web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\StudentController;
use Modules\Student\Http\Controllers\GuardianController;
use Modules\Student\Http\Controllers\EnrollmentController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Students
    Route::resource('students', StudentController::class);
    Route::get('students/{id}/profile', [StudentController::class, 'profile'])->name('students.profile');
    Route::post('students/{id}/assign-section', [StudentController::class, 'assignSection'])->name('students.assign-section');

    // Guardians
    Route::resource('guardians', GuardianController::class);
    Route::post('students/{studentId}/guardians/{guardianId}/attach', [GuardianController::class, 'attachToStudent']);

    // Enrollments
    Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('enrollments/promote', [EnrollmentController::class, 'promoteStudents'])->name('enrollments.promote');
});
```

---

## Permissions

```php
// Student Permissions
'view students'
'create students'
'edit students'
'delete students'
'assign student section'
'promote students'

// Guardian Permissions
'view guardians'
'create guardians'
'edit guardians'
'delete guardians'

// Enrollment Permissions
'view enrollments'
'create enrollments'
'edit enrollments'
'promote students'
```

---

## Integration Points

### From Admission Module

```php
// In AdmissionService::acceptApplication()
use Modules\Student\Services\StudentService;

public static function acceptApplication(int $id, int $reviewerId): AdmissionApplication
{
    DB::transaction(function () use ($id, $reviewerId) {
        $application = AdmissionApplication::findOrFail($id);

        // Create student from application
        $student = StudentService::createFromAdmission($application);

        // Update application
        $application->update([
            'status' => 'accepted',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'accepted_at' => now(),
            'student_id' => $student->id,
        ]);

        return $application;
    });
}
```

---

**End of Document**
