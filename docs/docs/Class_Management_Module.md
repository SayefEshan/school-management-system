# Class Management Module - Complete Specification
## Class, Section, Schedule, and Resource Management

**Version:** 1.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

The Class Management module handles all aspects of class organization, scheduling, teacher assignments, resource allocation, and section management for the school. This module works closely with the Student, User, and Academic modules to provide comprehensive class administration.

---

## Module Scope

### Core Functionalities

1. **Class & Section Management**
   - Class configuration (Class 6-10)
   - Section management per class (A, B, C, etc.)
   - Capacity tracking and student assignment
   - Class teacher assignment

2. **Class Schedules & Timetables**
   - Period-wise class schedules
   - Teacher assignment per period
   - Subject-wise scheduling
   - Room allocation
   - Weekly timetable generation

3. **Teacher Assignments**
   - Class teacher designation
   - Subject teacher assignment
   - Multiple class handling
   - Workload tracking

4. **Class Materials & Resources**
   - Digital study materials
   - Assignment upload/distribution
   - Notice board per class
   - Resource sharing

5. **Attendance Management** (Integration point)
   - Class-wise attendance
   - Period-wise attendance
   - Attendance reports

---

## Module Structure

```
Modules/ClassManagement/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ClassController.php
│   │   │   ├── SectionController.php
│   │   │   ├── ScheduleController.php
│   │   │   ├── ClassTeacherController.php
│   │   │   ├── SubjectController.php
│   │   │   ├── MaterialController.php
│   │   │   └── TimetableController.php
│   │   └── Requests/
│   │       ├── StoreScheduleRequest.php
│   │       ├── AssignTeacherRequest.php
│   │       └── UploadMaterialRequest.php
│   ├── Models/
│   │   ├── Subject.php
│   │   ├── ClassSchedule.php
│   │   ├── ClassTeacher.php
│   │   ├── SubjectTeacher.php
│   │   ├── ClassMaterial.php
│   │   ├── ClassNotice.php
│   │   └── Period.php
│   ├── Services/
│   │   ├── ScheduleService.php
│   │   ├── TimetableService.php
│   │   ├── TeacherAssignmentService.php
│   │   └── MaterialService.php
│   ├── Events/
│   │   ├── TeacherAssigned.php
│   │   ├── MaterialUploaded.php
│   │   └── ScheduleUpdated.php
│   └── Enums/
│       ├── DayOfWeek.php
│       ├── PeriodType.php
│       └── MaterialType.php
├── database/
│   ├── migrations/
│   │   ├── create_subjects_table.php
│   │   ├── create_periods_table.php
│   │   ├── create_class_schedules_table.php
│   │   ├── create_class_teachers_table.php
│   │   ├── create_subject_teachers_table.php
│   │   ├── create_class_materials_table.php
│   │   └── create_class_notices_table.php
│   └── seeders/
│       ├── SubjectSeeder.php
│       ├── PeriodSeeder.php
│       └── ClassManagementPermissionSeeder.php
├── resources/
│   └── views/
│       ├── classes/
│       │   └── dashboard.blade.php
│       ├── schedules/
│       │   ├── index.blade.php
│       │   ├── timetable.blade.php
│       │   └── create.blade.php
│       ├── teachers/
│       │   └── assignments.blade.php
│       └── materials/
│           ├── index.blade.php
│           └── upload.blade.php
└── routes/
    ├── web.php
    └── api.php
```

---

## Database Schema

### subjects Table

```sql
CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL, -- MATH, ENG, SCI
    name VARCHAR(255) NOT NULL, -- Mathematics
    name_bn VARCHAR(255) NOT NULL, -- গণিত
    description TEXT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE, -- Required for all students
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;
```

### periods Table
Defines the time slots for classes throughout the day.

```sql
CREATE TABLE periods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- 1st Period, Break, 2nd Period
    name_bn VARCHAR(100) NOT NULL, -- প্রথম পিরিয়ড
    period_number INT UNSIGNED NOT NULL, -- 1, 2, 3...
    start_time TIME NOT NULL, -- 08:00:00
    end_time TIME NOT NULL, -- 08:45:00
    duration INT UNSIGNED NOT NULL, -- Minutes: 45
    is_break BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_period_number (period_number),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;
```

### class_schedules Table
Maps class/section to subject/teacher per period per day.

```sql
CREATE TABLE class_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NOT NULL,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    period_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NULL, -- NULL for breaks
    teacher_id BIGINT UNSIGNED NULL, -- FK to users (teachers)
    room_number VARCHAR(50) NULL, -- Room 201, Lab 1
    notes TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (period_id) REFERENCES periods(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_schedule (academic_year_id, class_id, section_id, day_of_week, period_id),
    INDEX idx_class_section (class_id, section_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_day (day_of_week)
) ENGINE=InnoDB;
```

### class_teachers Table
Assigns class teachers (homeroom teachers) to classes/sections.

```sql
CREATE TABLE class_teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL, -- FK to users
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (section_id) REFERENCES sections(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id),

    UNIQUE KEY unique_class_teacher_year (academic_year_id, class_id, section_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_class_section (class_id, section_id)
) ENGINE=InnoDB;
```

### subject_teachers Table
Assigns subject teachers for specific subjects across classes.

```sql
CREATE TABLE subject_teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL, -- FK to users
    class_id BIGINT UNSIGNED NULL, -- NULL = teaches all classes
    section_id BIGINT UNSIGNED NULL, -- NULL = teaches all sections
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,

    INDEX idx_teacher (teacher_id),
    INDEX idx_subject (subject_id),
    INDEX idx_class (class_id)
) ENGINE=InnoDB;
```

### class_materials Table
Study materials, assignments, notes shared with classes.

```sql
CREATE TABLE class_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    material_type ENUM('notes', 'assignment', 'quiz', 'resource', 'video', 'link', 'document') NOT NULL,
    file_path VARCHAR(255) NULL, -- For uploaded files
    external_link VARCHAR(255) NULL, -- For external resources
    file_size INT UNSIGNED NULL, -- In bytes
    file_type VARCHAR(50) NULL, -- PDF, DOCX, MP4

    -- Association
    subject_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL, -- NULL = all classes
    section_id BIGINT UNSIGNED NULL, -- NULL = all sections in class
    academic_year_id BIGINT UNSIGNED NOT NULL,

    -- Uploaded by
    uploaded_by BIGINT UNSIGNED NOT NULL, -- FK to users (teacher)
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Visibility
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,

    -- Deadlines (for assignments)
    due_date DATETIME NULL,
    marks INT UNSIGNED NULL, -- Total marks for assignment

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id),

    INDEX idx_class_section (class_id, section_id),
    INDEX idx_subject (subject_id),
    INDEX idx_type (material_type),
    INDEX idx_published (is_published)
) ENGINE=InnoDB;
```

### class_notices Table
Notices and announcements for specific classes.

```sql
CREATE TABLE class_notices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_bn VARCHAR(255) NULL,
    content TEXT NOT NULL,
    content_bn TEXT NULL,

    -- Association
    class_id BIGINT UNSIGNED NULL, -- NULL = all classes
    section_id BIGINT UNSIGNED NULL, -- NULL = all sections
    academic_year_id BIGINT UNSIGNED NOT NULL,

    -- Priority and type
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    notice_type ENUM('general', 'exam', 'holiday', 'event', 'urgent') DEFAULT 'general',

    -- Posted by
    posted_by BIGINT UNSIGNED NOT NULL, -- FK to users
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Visibility
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL, -- Auto-hide after this date

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (posted_by) REFERENCES users(id),

    INDEX idx_class_section (class_id, section_id),
    INDEX idx_published (is_published),
    INDEX idx_priority (priority)
) ENGINE=InnoDB;
```

---

## Model Implementations

### Subject Model

```php
<?php

namespace Modules\ClassManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_bn',
        'description',
        'is_mandatory',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function teachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }

    public function materials()
    {
        return $this->hasMany(ClassMaterial::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }
}
```

### ClassSchedule Model

```php
<?php

namespace Modules\ClassManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Admission\Models\{AcademicYear, ClassModel, Section};
use App\Models\User;

class ClassSchedule extends Model
{
    protected $fillable = [
        'academic_year_id',
        'class_id',
        'section_id',
        'day_of_week',
        'period_id',
        'subject_id',
        'teacher_id',
        'room_number',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes
    public function scopeForClass($query, $classId, $sectionId)
    {
        return $query->where('class_id', $classId)
                    ->where('section_id', $sectionId);
    }

    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}
```

### ClassTeacher Model

```php
<?php

namespace Modules\ClassManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Admission\Models\{AcademicYear, ClassModel, Section};
use App\Models\User;

class ClassTeacher extends Model
{
    protected $fillable = [
        'academic_year_id',
        'class_id',
        'section_id',
        'teacher_id',
        'assigned_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Get students in this class
    public function students()
    {
        return $this->hasMany(Student::class, 'enrollment_section_id', 'section_id')
                    ->where('status', 'active');
    }
}
```

### ClassMaterial Model

```php
<?php

namespace Modules\ClassManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Admission\Models\{AcademicYear, ClassModel, Section};
use App\Models\User;

class ClassMaterial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'material_type',
        'file_path',
        'external_link',
        'file_size',
        'file_type',
        'subject_id',
        'class_id',
        'section_id',
        'academic_year_id',
        'uploaded_by',
        'uploaded_at',
        'is_published',
        'published_at',
        'due_date',
        'marks',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'published_at' => 'datetime',
        'due_date' => 'datetime',
        'is_published' => 'boolean',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForClass($query, $classId, $sectionId = null)
    {
        $query = $query->where(function ($q) use ($classId) {
            $q->where('class_id', $classId)->orWhereNull('class_id');
        });

        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)->orWhereNull('section_id');
            });
        }

        return $query;
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>=', now())
                    ->orderBy('due_date');
    }
}
```

---

## Service Implementations

### TimetableService

```php
<?php

namespace Modules\ClassManagement\Services;

use Modules\ClassManagement\Models\{ClassSchedule, Period};
use Illuminate\Support\Collection;

class TimetableService
{
    /**
     * Generate weekly timetable for a class/section
     */
    public static function generateTimetable(
        int $academicYearId,
        int $classId,
        int $sectionId
    ): array {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $periods = Period::where('is_active', true)
                        ->orderBy('period_number')
                        ->get();

        $timetable = [];

        foreach ($days as $day) {
            $timetable[$day] = [];

            foreach ($periods as $period) {
                $schedule = ClassSchedule::where([
                    'academic_year_id' => $academicYearId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'day_of_week' => $day,
                    'period_id' => $period->id,
                ])->with(['subject', 'teacher'])->first();

                $timetable[$day][] = [
                    'period' => $period,
                    'schedule' => $schedule,
                    'subject' => $schedule?->subject,
                    'teacher' => $schedule?->teacher,
                    'room' => $schedule?->room_number,
                ];
            }
        }

        return $timetable;
    }

    /**
     * Get teacher's weekly schedule
     */
    public static function getTeacherSchedule(int $teacherId, int $academicYearId): Collection
    {
        return ClassSchedule::where('teacher_id', $teacherId)
                           ->where('academic_year_id', $academicYearId)
                           ->with(['class', 'section', 'period', 'subject'])
                           ->orderBy('day_of_week')
                           ->orderBy('period_id')
                           ->get()
                           ->groupBy('day_of_week');
    }

    /**
     * Check for schedule conflicts
     */
    public static function checkConflict(array $scheduleData): ?array
    {
        // Check teacher availability
        $teacherConflict = ClassSchedule::where([
            'academic_year_id' => $scheduleData['academic_year_id'],
            'day_of_week' => $scheduleData['day_of_week'],
            'period_id' => $scheduleData['period_id'],
            'teacher_id' => $scheduleData['teacher_id'],
        ])
        ->where('id', '!=', $scheduleData['id'] ?? 0)
        ->with(['class', 'section'])
        ->first();

        if ($teacherConflict) {
            return [
                'type' => 'teacher',
                'message' => 'Teacher already assigned to another class during this period',
                'conflict' => $teacherConflict,
            ];
        }

        // Check class availability
        $classConflict = ClassSchedule::where([
            'academic_year_id' => $scheduleData['academic_year_id'],
            'class_id' => $scheduleData['class_id'],
            'section_id' => $scheduleData['section_id'],
            'day_of_week' => $scheduleData['day_of_week'],
            'period_id' => $scheduleData['period_id'],
        ])
        ->where('id', '!=', $scheduleData['id'] ?? 0)
        ->with(['teacher', 'subject'])
        ->first();

        if ($classConflict) {
            return [
                'type' => 'class',
                'message' => 'This class already has a subject scheduled for this period',
                'conflict' => $classConflict,
            ];
        }

        return null;
    }
}
```

### TeacherAssignmentService

```php
<?php

namespace Modules\ClassManagement\Services;

use Modules\ClassManagement\Models\{ClassTeacher, SubjectTeacher};
use Modules\ClassManagement\Events\TeacherAssigned;
use DB;

class TeacherAssignmentService
{
    /**
     * Assign class teacher
     */
    public static function assignClassTeacher(array $data): ClassTeacher
    {
        // Deactivate existing class teacher for this section
        ClassTeacher::where([
            'academic_year_id' => $data['academic_year_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
        ])->update(['is_active' => false]);

        // Create new assignment
        $assignment = ClassTeacher::create([
            'academic_year_id' => $data['academic_year_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
            'teacher_id' => $data['teacher_id'],
            'assigned_at' => now(),
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        event(new TeacherAssigned($assignment));

        return $assignment;
    }

    /**
     * Assign subject teacher
     */
    public static function assignSubjectTeacher(array $data): SubjectTeacher
    {
        $assignment = SubjectTeacher::create([
            'academic_year_id' => $data['academic_year_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'],
            'class_id' => $data['class_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'assigned_at' => now(),
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        return $assignment;
    }

    /**
     * Get teacher workload summary
     */
    public static function getTeacherWorkload(int $teacherId, int $academicYearId): array
    {
        $classTeacher = ClassTeacher::where('teacher_id', $teacherId)
                                   ->where('academic_year_id', $academicYearId)
                                   ->where('is_active', true)
                                   ->with(['class', 'section'])
                                   ->get();

        $subjectTeacher = SubjectTeacher::where('teacher_id', $teacherId)
                                       ->where('academic_year_id', $academicYearId)
                                       ->where('is_active', true)
                                       ->with(['subject', 'class', 'section'])
                                       ->get();

        $scheduleCount = ClassSchedule::where('teacher_id', $teacherId)
                                     ->where('academic_year_id', $academicYearId)
                                     ->count();

        return [
            'class_teacher' => $classTeacher,
            'subject_teacher' => $subjectTeacher,
            'total_periods_per_week' => $scheduleCount,
        ];
    }
}
```

---

## API Endpoints

### Schedule Management

```php
// Get timetable for a class
GET /api/class-management/timetable/{classId}/{sectionId}

// Create/update schedule
POST /api/class-management/schedules
PUT /api/class-management/schedules/{id}

// Get teacher's schedule
GET /api/class-management/teachers/{teacherId}/schedule

// Check schedule conflicts
POST /api/class-management/schedules/check-conflict
```

### Teacher Assignments

```php
// Assign class teacher
POST /api/class-management/class-teachers

// Assign subject teacher
POST /api/class-management/subject-teachers

// Get teacher workload
GET /api/class-management/teachers/{teacherId}/workload
```

### Materials

```php
// List materials for class
GET /api/class-management/materials?class_id={id}&section_id={id}

// Upload material
POST /api/class-management/materials

// Get material details
GET /api/class-management/materials/{id}

// Delete material
DELETE /api/class-management/materials/{id}
```

---

## Permissions

```php
Permissions for Class Management Module:

// Schedules
- 'view class schedules'
- 'create class schedule'
- 'edit class schedule'
- 'delete class schedule'

// Teachers
- 'assign class teacher'
- 'assign subject teacher'
- 'view teacher assignments'
- 'remove teacher assignment'

// Materials
- 'upload class materials'
- 'view class materials'
- 'edit class materials'
- 'delete class materials'

// Notices
- 'post class notice'
- 'view class notices'
- 'edit class notice'
- 'delete class notice'
```

---

## Integration Points

### With Student Module
- Student enrollment by section
- Class/section assignment
- Student list for class teachers

### With User Module (Teachers)
- Teacher profile and assignments
- Workload calculation
- Availability checking

### With Admission Module
- Class and section definitions
- Academic year context
- Section capacity management

### With Attendance Module (Future)
- Period-wise attendance
- Subject-wise attendance
- Class teacher access

---

## Typical Workflows

### 1. Setting Up Class Schedule

```
1. Admin creates periods (1st Period, Break, 2nd Period...)
2. Admin creates/updates subjects (Math, English, Science...)
3. Admin assigns class teachers to sections
4. Admin assigns subject teachers for each subject
5. Admin creates weekly schedule:
   - Select class & section
   - Select day & period
   - Select subject & teacher
   - Assign room (optional)
   - Save schedule
6. System checks for conflicts
7. Timetable generated automatically
```

### 2. Uploading Class Material

```
1. Teacher logs in
2. Navigates to assigned class/section
3. Selects material type (notes, assignment, etc.)
4. Uploads file or provides link
5. Sets visibility (published/draft)
6. Sets deadline (for assignments)
7. Material becomes visible to students
```

### 3. Viewing Teacher Workload

```
1. Admin selects teacher
2. System shows:
   - Class teacher assignments
   - Subject teacher assignments
   - Weekly schedule (period count)
   - Total workload hours
```

---

## Benefits

1. **Organized Scheduling**
   - Conflict-free timetables
   - Easy visualization
   - Quick modifications

2. **Teacher Management**
   - Clear responsibilities
   - Workload tracking
   - Assignment history

3. **Resource Sharing**
   - Centralized materials
   - Easy distribution
   - Version control

4. **Section Management**
   - Dedicated class teachers
   - Section-specific resources
   - Targeted communications

5. **Reporting**
   - Teacher workload reports
   - Class utilization
   - Material usage statistics

---

## Future Enhancements

1. **Automated Schedule Generation**
   - AI-based conflict resolution
   - Optimal time slot allocation
   - Teacher preference consideration

2. **Online Classes Integration**
   - Virtual classroom links
   - Recorded lectures
   - Live class scheduling

3. **Assignment Submission**
   - Student file uploads
   - Grading interface
   - Plagiarism detection

4. **Parent Access**
   - View class schedule
   - Access materials
   - Track assignments

5. **Mobile App**
   - Schedule on the go
   - Material downloads
   - Push notifications

---

**End of Document**
