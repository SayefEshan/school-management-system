# Student ID Generation Logic
## Numeric Student ID with Embedded Information

**Version:** 1.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

This document defines the logic for generating unique, numeric student IDs that embed meaningful information about the student's admission year and class.

---

## ID Format

### Structure: YYCCSSSSS

```
YY    = Admission Year (last 2 digits)
CC    = Admission Class Code (06-10)
SSSSS = Sequential Serial Number (5 digits)

Total Length: 9 digits
```

### Examples

```
Student ID: 2506000001
├─ 25: Admitted in 2025
├─ 06: Admitted to Class 6
└─ 00001: First student

Student ID: 2510000123
├─ 25: Admitted in 2025
├─ 10: Admitted to Class 10
└─ 00123: 123rd student

Student ID: 2607000001
├─ 26: Admitted in 2026
├─ 07: Admitted to Class 7
└─ 00001: First student (new year)
```

---

## Class Code Mapping

### Standard Classes

| Class Name | Numeric Code | Code in ID |
|------------|--------------|------------|
| Class 6    | 6            | 06         |
| Class 7    | 7            | 07         |
| Class 8    | 8            | 08         |
| Class 9    | 9            | 09         |
| Class 10   | 10           | 10         |

### Database Mapping

```sql
classes table:
- id: 1, name: "Class 6", name_bn: "ষষ্ঠ", numeric_code: 6
- id: 2, name: "Class 7", name_bn: "সপ্তম", numeric_code: 7
- id: 3, name: "Class 8", name_bn: "অষ্টম", numeric_code: 8
- id: 4, name: "Class 9", name_bn: "নবম", numeric_code: 9
- id: 5, name: "Class 10", name_bn: "দশম", numeric_code: 10
```

---

## Generation Algorithm

### Step-by-Step Process

```
1. Extract Year Component
   ├─ Get current admission year: 2025
   └─ Take last 2 digits: 25

2. Extract Class Code
   ├─ Get student's admission class: Class 6
   ├─ Look up numeric_code from classes table: 6
   └─ Zero-pad to 2 digits: 06

3. Generate Serial Number
   ├─ Query last student ID for same year + class
   ├─ Extract serial number from last ID
   ├─ Increment by 1
   └─ Zero-pad to 5 digits: 00001

4. Concatenate Components
   └─ YY + CC + SSSSS = 2506000001
```

### PHP Implementation

```php
<?php

namespace Modules\Student\Services;

use Modules\Student\Models\Student;
use Modules\Admission\Models\ClassModel;

class StudentIDService
{
    /**
     * Generate unique student ID
     *
     * @param int $admissionYear
     * @param int $classId
     * @return string
     */
    public static function generateStudentID(int $admissionYear, int $classId): string
    {
        // Step 1: Get year component (last 2 digits)
        $yearComponent = substr((string)$admissionYear, -2);

        // Step 2: Get class code
        $class = ClassModel::findOrFail($classId);
        $classComponent = str_pad($class->numeric_code, 2, '0', STR_PAD_LEFT);

        // Step 3: Generate serial number
        $prefix = $yearComponent . $classComponent;
        $lastStudent = Student::where('student_id', 'LIKE', $prefix . '%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($lastStudent) {
            // Extract last 5 digits and increment
            $lastSerial = intval(substr($lastStudent->student_id, -5));
            $newSerial = $lastSerial + 1;
        } else {
            // First student for this year + class
            $newSerial = 1;
        }

        $serialComponent = str_pad($newSerial, 5, '0', STR_PAD_LEFT);

        // Step 4: Concatenate
        $studentID = $yearComponent . $classComponent . $serialComponent;

        // Verify uniqueness (shouldn't happen, but safety check)
        if (Student::where('student_id', $studentID)->exists()) {
            throw new \Exception("Generated student ID already exists: {$studentID}");
        }

        return $studentID;
    }

    /**
     * Parse student ID to extract components
     *
     * @param string $studentID
     * @return array
     */
    public static function parseStudentID(string $studentID): array
    {
        if (strlen($studentID) !== 9) {
            throw new \InvalidArgumentException("Invalid student ID format");
        }

        return [
            'year' => '20' . substr($studentID, 0, 2),
            'class_code' => intval(substr($studentID, 2, 2)),
            'serial' => intval(substr($studentID, 4, 5)),
            'full_id' => $studentID
        ];
    }

    /**
     * Validate student ID format
     *
     * @param string $studentID
     * @return bool
     */
    public static function isValidFormat(string $studentID): bool
    {
        // Must be exactly 9 digits
        if (!preg_match('/^\d{9}$/', $studentID)) {
            return false;
        }

        // Year should be reasonable (20-99 for 2020-2099)
        $year = intval(substr($studentID, 0, 2));
        if ($year < 20 || $year > 99) {
            return false;
        }

        // Class code should be 06-10
        $classCode = intval(substr($studentID, 2, 2));
        if ($classCode < 6 || $classCode > 10) {
            return false;
        }

        return true;
    }
}
```

---

## Serial Number Management

### Per Year-Class Sequence

Each combination of year and class has its own independent sequence:

```
Year 2025, Class 6:
2506000001, 2506000002, 2506000003, ..., 2506099999

Year 2025, Class 7:
2507000001, 2507000002, 2507000003, ..., 2507099999

Year 2026, Class 6:
2606000001, 2606000002, ... (sequence resets for new year)
```

### Maximum Capacity

With 5-digit serial (00001-99999):
- **99,999 students** per year-class combination
- Well beyond typical school capacity

### Handling Edge Cases

```php
// If serial exceeds 99999 (highly unlikely)
if ($newSerial > 99999) {
    throw new \Exception(
        "Serial number limit exceeded for {$admissionYear} Class {$classCode}. " .
        "Maximum 99,999 students per year-class combination."
    );
}
```

---

## Collision Prevention

### Uniqueness Guarantee

1. **Database Constraint**
   ```sql
   ALTER TABLE students
   ADD UNIQUE INDEX idx_unique_student_id (student_id);
   ```

2. **Transaction Lock**
   ```php
   DB::transaction(function () use ($admissionYear, $classId) {
       $studentID = StudentIDService::generateStudentID($admissionYear, $classId);

       // Create student with generated ID
       $student = Student::create([
           'student_id' => $studentID,
           // ... other fields
       ]);

       return $student;
   });
   ```

3. **Database-level Generation** (Alternative)
   ```sql
   -- Use database sequence for serial number
   CREATE SEQUENCE student_seq_2506 START 1;
   -- New sequence per year-class
   ```

---

## Migration Considerations

### From Old System

If migrating from an old system with different ID format:

```php
class MigrateOldStudentIDs
{
    public function migrate()
    {
        $oldStudents = OldStudent::all();

        foreach ($oldStudents as $oldStudent) {
            // Generate new ID based on their admission year and class
            $newID = StudentIDService::generateStudentID(
                $oldStudent->admission_year,
                $oldStudent->class_id
            );

            // Store mapping for reference
            StudentIDMapping::create([
                'old_student_id' => $oldStudent->id,
                'new_student_id' => $newID,
                'migrated_at' => now()
            ]);

            // Update student record
            Student::create([
                'student_id' => $newID,
                'old_reference_id' => $oldStudent->id,
                // ... map other fields
            ]);
        }
    }
}
```

### Preserving Historical Data

```sql
-- Keep old ID as reference
students table:
- student_id (new numeric ID)
- old_student_id (varchar, nullable, for migrated records)
- migrated_from_old_system (boolean)
```

---

## Display Format

### Human-Readable Display

While stored as pure numeric, display with formatting for readability:

```php
function formatStudentID(string $studentID): string
{
    // Display as: 25-06-00001
    return substr($studentID, 0, 2) . '-' .
           substr($studentID, 2, 2) . '-' .
           substr($studentID, 4, 5);
}

// Display
echo formatStudentID('2506000001'); // Output: 25-06-00001
```

### In Reports/Documents

```
Student Information
-------------------
Student ID: 25-06-00001
Name: Md. Abdul Rahman
Class: 6, Section: A
Roll No: 23
```

---

## Usage Examples

### Example 1: New Admission for Class 6 in 2025

```php
// Application approved, creating student
$application = AdmissionApplication::find($id);
$admissionYear = $application->academicYear->year; // 2025
$classId = $application->applying_class_id; // Class 6

// Generate student ID
$studentID = StudentIDService::generateStudentID($admissionYear, $classId);
// Result: 2506000015 (15th student admitted to Class 6 in 2025)

// Create student
$student = Student::create([
    'student_id' => $studentID,
    'name_en' => $application->student_name_en,
    'name_bn' => $application->student_name_bn,
    // ... other fields
]);
```

### Example 2: Parsing Student ID

```php
$studentID = '2506000015';
$parsed = StudentIDService::parseStudentID($studentID);

/*
Array (
    'year' => '2025',
    'class_code' => 6,
    'serial' => 15,
    'full_id' => '2506000015'
)
*/

// Use parsed data
echo "Student admitted to Class {$parsed['class_code']} in {$parsed['year']}";
// Output: Student admitted to Class 6 in 2025
```

### Example 3: Searching by ID

```php
// Search student by ID
$student = Student::where('student_id', '2506000015')->first();

// Search all students from a specific year-class
$year = 25; // 2025
$class = 06; // Class 6
$prefix = $year . $class;

$students = Student::where('student_id', 'LIKE', $prefix . '%')
    ->orderBy('student_id')
    ->get();
// Returns all Class 6 students admitted in 2025
```

---

## Database Schema

### students table

```sql
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(9) UNIQUE NOT NULL, -- The generated numeric ID

    -- Student information
    name_en VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,

    -- Admission details
    admission_year INT UNSIGNED NOT NULL,
    admission_class_id BIGINT UNSIGNED NOT NULL,

    -- Status
    status ENUM('active', 'withdrawn', 'graduated', 'transferred') DEFAULT 'active',

    -- For migration from old system
    old_student_id VARCHAR(50) NULL,
    migrated_from_old_system BOOLEAN DEFAULT FALSE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE INDEX idx_student_id (student_id),
    INDEX idx_admission_year_class (admission_year, admission_class_id),
    INDEX idx_status (status),

    -- Foreign keys
    FOREIGN KEY (admission_class_id) REFERENCES classes(id)
) ENGINE=InnoDB;
```

---

## Testing

### Unit Tests

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Modules\Student\Services\StudentIDService;

class StudentIDServiceTest extends TestCase
{
    /** @test */
    public function it_generates_valid_student_id_format()
    {
        $studentID = StudentIDService::generateStudentID(2025, 1); // Class 6

        $this->assertEquals(9, strlen($studentID));
        $this->assertMatchesRegularExpression('/^\d{9}$/', $studentID);
        $this->assertTrue(StudentIDService::isValidFormat($studentID));
    }

    /** @test */
    public function it_generates_unique_sequential_ids()
    {
        $id1 = StudentIDService::generateStudentID(2025, 1);
        $id2 = StudentIDService::generateStudentID(2025, 1);

        $this->assertNotEquals($id1, $id2);
        $this->assertEquals(intval(substr($id2, -5)), intval(substr($id1, -5)) + 1);
    }

    /** @test */
    public function it_parses_student_id_correctly()
    {
        $studentID = '2506000015';
        $parsed = StudentIDService::parseStudentID($studentID);

        $this->assertEquals('2025', $parsed['year']);
        $this->assertEquals(6, $parsed['class_code']);
        $this->assertEquals(15, $parsed['serial']);
    }

    /** @test */
    public function it_validates_student_id_format()
    {
        $this->assertTrue(StudentIDService::isValidFormat('2506000001'));
        $this->assertTrue(StudentIDService::isValidFormat('2510099999'));

        $this->assertFalse(StudentIDService::isValidFormat('123456789')); // Wrong year
        $this->assertFalse(StudentIDService::isValidFormat('2599000001')); // Invalid class
        $this->assertFalse(StudentIDService::isValidFormat('25060001')); // Too short
    }

    /** @test */
    public function different_classes_have_independent_sequences()
    {
        $class6ID = StudentIDService::generateStudentID(2025, 1); // Class 6
        $class7ID = StudentIDService::generateStudentID(2025, 2); // Class 7

        // Both should start at 00001
        $this->assertEquals('2506000001', $class6ID);
        $this->assertEquals('2507000001', $class7ID);
    }
}
```

---

## Performance Considerations

### Query Optimization

```sql
-- Index for fast last student lookup
CREATE INDEX idx_student_id_prefix
ON students (student_id(4)); -- Index first 4 chars (YYCC)

-- Query uses index
SELECT student_id
FROM students
WHERE student_id LIKE '2506%'
ORDER BY student_id DESC
LIMIT 1;
```

### Caching Last Serial

```php
// Cache last serial number per year-class
Cache::remember("last_serial_2506", 3600, function () {
    return Student::where('student_id', 'LIKE', '2506%')
        ->max('student_id');
});
```

---

## Benefits of This Format

### 1. Purely Numeric
✅ Easy data entry (no letters to confuse)
✅ Works with numeric keypad
✅ SMS-friendly (no encoding issues)

### 2. Embedded Information
✅ Year visible at a glance
✅ Class visible at a glance
✅ Sortable (older students have lower IDs)

### 3. Scalability
✅ 99,999 students per year-class
✅ Works for decades (until 2099)
✅ No conflicts between years/classes

### 4. Migration-Friendly
✅ Can coexist with old IDs
✅ Clear mapping between old and new
✅ Preserves historical data

---

## Future Enhancements

### Adding Campus Code (for multi-campus schools)

```
Format: CCYYCCSSSSS (11 digits)

CC = Campus Code (01-99)
YY = Year
CC = Class
SSSSS = Serial

Example: 012506000001
├─ 01: Main Campus
├─ 25: 2025
├─ 06: Class 6
└─ 00001: Serial
```

### QR Code Integration

```php
// Generate QR code with student ID
$qr = QrCode::size(200)->generate($student->student_id);

// Scan to quickly lookup student
$scannedID = '2506000015';
$student = Student::where('student_id', $scannedID)->first();
```

---

**End of Document**
