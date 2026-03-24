# Database Entity Relationship Diagram
## Complete Schema for School Management System

**Version:** 2.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

This document provides a comprehensive view of all database tables, their relationships, and key constraints for the school management system using the **Approve-First, Pay-Later** workflow with fee package support.

---

## Module-wise Tables

### Core System (Laravel)
- users
- profiles
- password_reset_tokens
- sessions
- cache

### RolePermission Module
- roles
- permissions
- role_has_permissions
- model_has_roles
- model_has_permissions

### ActivityLog Module
- audits

### Settings Module
- settings

### Admission Module
- academic_years
- classes
- sections
- admission_applications
- application_form_fees (optional)

### Student Module (To be created)
- students
- guardians
- student_guardian (pivot)
- enrollments

### Finance Module (To be created)
- fee_types
- fee_packages
- fee_package_items
- fee_structures
- student_fees
- payment_transactions
- payment_line_items
- incomes
- expense_categories
- expenses
- monthly_financial_summaries

### Class Management Module (To be created)
- class_schedules
- class_teachers
- subjects
- class_materials

---

## Complete ERD

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CORE TABLES                                  │
└─────────────────────────────────────────────────────────────────────┘

┌───────────────────┐
│      users        │
├───────────────────┤
│ id (PK)           │───┐
│ name              │   │
│ email             │   │ 1
│ password          │   │
│ email_verified_at │   │
│ created_at        │   │
│ updated_at        │   │
└───────────────────┘   │
                        │
                        │ *
               ┌────────▼────────┐
               │    profiles     │
               ├─────────────────┤
               │ id (PK)         │
               │ user_id (FK)    │
               │ phone           │
               │ address         │
               │ photo           │
               └─────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                   ACADEMIC STRUCTURE                                │
└─────────────────────────────────────────────────────────────────────┘

┌────────────────────┐
│  academic_years    │
├────────────────────┤
│ id (PK)            │──────┐
│ name (2025)        │      │
│ name_bn (২০২৫)     │      │
│ start_date         │      │
│ end_date           │      │
│ is_current         │      │
└────────────────────┘      │
                            │ 1
                            │
┌────────────────────┐      │
│      classes       │      │
├────────────────────┤      │
│ id (PK)            │──┐   │
│ name (Class 6)     │  │   │
│ name_bn (ষষ্ঠ)     │  │   │
│ numeric_code (6)   │  │ 1 │
│ order              │  │   │
│ is_active          │  │   │
└────────────────────┘  │   │
                        │   │
                        │   │
                        │ * │
┌────────────────────┐  │   │
│     sections       │  │   │
├────────────────────┤  │   │
│ id (PK)            │  │   │
│ class_id (FK)      │◄─┘   │
│ name (A, B, C)     │      │
│ name_bn (ক, খ, গ)  │      │
│ capacity           │      │
│ current_count      │      │
│ is_active          │      │
└────────────────────┘      │
                            │
                            │
┌─────────────────────────────────────────────────────────────────────┐
│                   ADMISSION MODULE                                  │
└─────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────┐
│    admission_applications              │
├────────────────────────────────────────┤
│ id (PK)                                │───────┐
│ tracking_code (unique)                 │       │
│ academic_year_id (FK) ─────────────────┼───────┼─────> academic_years
│ applying_class_id (FK) ────────────────┼───────┼─────> classes
│ applying_section_id (FK) ──────────────┼───────┼─────> sections
│                                        │       │
│ student_name_en                        │       │ 1
│ student_name_bn                        │       │
│ birth_registration_no                  │       │
│ date_of_birth                          │       │
│ gender, religion, ethnicity            │       │
│                                        │       │
│ father_name_en, father_name_bn         │       │
│ father_nid, father_phone               │       │
│ mother_name_en, mother_name_bn         │       │
│ mother_nid, mother_phone               │       │
│ guardian_name_en, guardian_name_bn     │       │
│ who_guardian                           │       │
│                                        │       │
│ present_village, present_district      │       │
│ permanent_village, permanent_...       │       │
│ primary_phone, email                   │       │
│                                        │       │
│ photo_path                             │       │
│ birth_certificate_path                 │       │
│ academic_transcript_path               │       │
│                                        │       │
│ status (enum)                          │       │
│ submitted_at, reviewed_at              │       │
│ reviewed_by (FK) ──────────────────────┼───────┼─────> users
│ accepted_at, rejected_at               │       │
│ created_student_id (FK) ───────────────┼───────┼─────> students
│                                        │       │
│ -- Signature Tracking --               │       │
│ principal_signed (boolean)             │       │
│ principal_signed_at (timestamp)        │       │
│ principal_signature_path (varchar)     │       │
│ class_teacher_signed (boolean)         │       │
│ class_teacher_signed_at (timestamp)    │       │
│ class_teacher_id (FK) ─────────────────┼───────┼─────> users
│ class_teacher_signature_path (varchar) │       │
└────────────────────────────────────────┘       │
                                                 │
                                                 │
┌──────────────────────────────────────┐         │
│  application_form_fees (OPTIONAL)    │         │
├──────────────────────────────────────┤         │
│ id (PK)                              │         │
│ form_number (unique)                 │         │
│ applicant_name (varchar, nullable)   │         │
│ applicant_phone (varchar, nullable)  │         │
│ amount_paid (decimal)                │         │
│ payment_method (enum)                │         │
│ payment_date (date)                  │         │
│ collected_by (FK) ───────────────────┼─────────┼─────> users
│ form_issued (boolean)                │         │
│ form_submitted (boolean)             │         │
│ admission_application_id (FK) ───────┼─────────┘
│ notes (text)                         │
│ created_at, updated_at               │
└──────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                    STUDENT MODULE                                   │
└─────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────┐
│         students                   │
├────────────────────────────────────┤
│ id (PK)                            │◄──────┐
│ student_id (unique, 9-digit)       │       │
│                                    │       │
│ name_en                            │       │ 1
│ name_bn                            │       │
│ date_of_birth                      │       │
│ birth_registration_no (unique)     │       │
│ gender, religion, ethnicity        │       │
│ blood_group                        │       │
│                                    │       │
│ phone, email                       │       │
│ present_address_en, _bn            │       │
│ permanent_address_en, _bn          │       │
│                                    │       │
│ primary_guardian_id (FK) ──────────┼──┐    │
│ photo_path                         │  │    │
│ birth_certificate_path             │  │    │
│                                    │  │    │
│ admission_year                     │  │    │
│ admission_class_id (FK) ───────────┼──┼────┼─────> classes
│ admission_application_id (FK) ─────┼──┼────┼─────> admission_applications
│                                    │  │    │
│ status (enum)                      │  │    │
│ withdrawal_date, withdrawal_reason │  │    │
└────────────────────────────────────┘  │    │
                 │                      │    │
                 │ *                    │    │
                 │                      │    │
                 │                      │    │
         ┌───────▼──────────────────┐   │    │
         │  student_guardian (pivot)│   │    │
         ├──────────────────────────┤   │    │
         │ id (PK)                  │   │    │
         │ student_id (FK)          │───┘    │
         │ guardian_id (FK)         │────────┼─────┐
         │ relation (enum)          │        │     │
         │ is_primary               │        │     │
         │ is_emergency_contact     │        │     │
         └──────────────────────────┘        │     │
                                             │     │
                                             │     │ *
┌────────────────────────────────┐           │ ┌───▼────────────┐
│       enrollments              │           │ │   guardians    │
├────────────────────────────────┤           │ ├────────────────┤
│ id (PK)                        │           │ │ id (PK)        │◄─┘
│ student_id (FK) ───────────────┼───────────┘ │ name_en        │
│ academic_year_id (FK) ─────────┼─────────────┼─> academic_... │
│ class_id (FK) ─────────────────┼─────────────┼─> classes      │
│ section_id (FK) ───────────────┼─────────────┼─> sections     │
│ roll_no                        │           │ │ name_bn        │
│ status (enum)                  │           │ │ nid            │
│ enrollment_date                │           │ │ phone, email   │
│ promotion_date                 │           │ │ address_en,_bn │
│ withdrawal_date                │           │ │ occupation     │
└────────────────────────────────┘           │ │ workplace      │
                                             │ │ yearly_income  │
                                             │ └────────────────┘
                                             │
┌─────────────────────────────────────────────────────────────────────┐
│                    FINANCE MODULE                                   │
└─────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────┐
│       fee_types                │
├────────────────────────────────┤
│ id (PK)                        │◄──────────┐
│ code (ADM_FORM, TUITION...)    │           │
│ name (Admission Form...)       │           │ 1
│ name_bn (ভর্তি ফরম...)         │           │
│ category (enum - 14 types)     │           │
│ default_amount                 │           │
│ is_one_time                    │           │
│ is_active                      │           │
│ sort_order                     │           │
└────────────────────────────────┘           │
                 │                           │
                 │ 1                         │
                 │                           │
                 │ *                         │
┌────────────────▼───────────────┐           │
│    fee_packages                │           │
├────────────────────────────────┤           │
│ id (PK)                        │◄──────────┼──┐
│ code (NEW_ADMIT_2025...)       │           │  │
│ name (New Student Package...)  │           │  │ 1
│ name_bn (নতুন শিক্ষার্থী...)  │           │  │
│ description                    │           │  │
│ package_type (enum)            │           │  │
│ academic_year_id (FK, null)────┼───────────┼──┼───> academic_years
│ class_id (FK, nullable) ───────┼───────────┼──┼───> classes
│ total_amount                   │           │  │
│ is_active                      │           │  │
│ sort_order                     │           │  │
└────────────────────────────────┘           │  │
                 │                           │  │
                 │ 1                         │  │
                 │                           │  │
                 │ *                         │  │
┌────────────────▼───────────────┐           │  │
│    fee_package_items           │           │  │
├────────────────────────────────┤           │  │
│ id (PK)                        │           │  │
│ fee_package_id (FK) ───────────┼───────────┼──┘
│ fee_type_id (FK) ──────────────┼───────────┘
│ quantity                       │
│ amount                         │
│ sort_order                     │
└────────────────────────────────┘

┌────────────────────────────────┐
│    fee_structures              │
├────────────────────────────────┤
│ id (PK)                        │
│ fee_type_id (FK) ──────────────┼───────────────> fee_types
│ academic_year_id (FK) ─────────┼───────────────> academic_years
│ class_id (FK, nullable) ───────┼───────────────> classes
│ amount                         │
│ discount_percentage            │
│ due_date_rule                  │
│ applicable_from                │
│ applicable_to                  │
│ is_active                      │
└────────────────────────────────┘
                 │
                 │ 1
                 │
                 │ *
┌────────────────▼───────────────┐
│      student_fees              │
├────────────────────────────────┤
│ id (PK)                        │
│ student_id (FK) ───────────────┼───────────────> students
│ fee_type_id (FK) ──────────────┼───────────────> fee_types
│ academic_year_id (FK) ─────────┼───────────────> academic_years
│ month (1-12, nullable)         │
│ amount                         │
│ paid_amount                    │
│ due_amount                     │
│ due_date                       │
│ status (enum)                  │
└────────────────────────────────┘


┌────────────────────────────────────────┐
│     payment_transactions               │
├────────────────────────────────────────┤
│ id (PK)                                │◄──────────┐
│ transaction_number (unique)            │           │
│ student_id (FK) ───────────────────────┼───────────┼────> students
│ total_amount                           │           │ 1
│ payment_method (enum)                  │           │
│ payment_date                           │           │
│ reference_number                       │           │
│ collected_by (FK) ─────────────────────┼───────────┼────> users
│ fee_package_id (FK, nullable) ─────────┼───────────┼────> fee_packages
│ receipt_generated                      │           │
│ receipt_path                           │           │
│ notes                                  │           │
└────────────────────────────────────────┘           │
                   │                                 │
                   │ 1                               │
                   │                                 │
                   │ *                               │
┌──────────────────▼─────────────────┐               │
│    payment_line_items              │               │
├────────────────────────────────────┤               │
│ id (PK)                            │───────────────┘
│ payment_transaction_id (FK)        │
│ fee_type_id (FK) ──────────────────┼───────────────────> fee_types
│ description                        │
│ quantity                           │
│ unit_amount                        │
│ total_amount                       │
└────────────────────────────────────┘


┌────────────────────────────────┐
│         incomes                │
├────────────────────────────────┤
│ id (PK)                        │
│ category (enum)                │
│ source                         │
│ amount                         │
│ income_date                    │
│ payment_method                 │
│ reference_number               │
│ receipt_number                 │
│ description                    │
│ recorded_by (FK) ──────────────┼────────────────> users
└────────────────────────────────┘


┌────────────────────────────────┐
│    expense_categories          │
├────────────────────────────────┤
│ id (PK)                        │◄──────────┐
│ name (Salary, Utilities...)    │           │
│ name_bn (বেতন, ইউটিলিটি...)   │           │ 1
│ code (SAL, UTL...)             │           │
│ is_active                      │           │
└────────────────────────────────┘           │
                                             │
                                             │ *
┌────────────────────────────────┐           │
│          expenses              │           │
├────────────────────────────────┤           │
│ id (PK)                        │           │
│ category_id (FK) ──────────────┼───────────┘
│ title                          │
│ amount                         │
│ expense_date                   │
│ payment_method                 │
│ paid_to                        │
│ reference_number               │
│ approved_by (FK) ──────────────┼────────────────> users
│ recorded_by (FK) ──────────────┼────────────────> users
│ notes                          │
│ attachment_path                │
└────────────────────────────────┘


┌────────────────────────────────────┐
│  monthly_financial_summaries       │
├────────────────────────────────────┤
│ id (PK)                            │
│ year (2025)                        │
│ month (1-12)                       │
│ total_fee_collection               │
│ total_other_income                 │
│ total_income                       │
│ total_expenses                     │
│ net_balance                        │
│ outstanding_fees                   │
│ overdue_fees                       │
│ fee_collection_rate (%)            │
└────────────────────────────────────┘
```

---

## Detailed Table Schemas

### admission_applications

```sql
CREATE TABLE admission_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(50) UNIQUE NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    applying_class_id BIGINT UNSIGNED NOT NULL,
    applying_section_id BIGINT UNSIGNED NULL,

    -- Student Information
    student_name_en VARCHAR(255) NOT NULL,
    student_name_bn VARCHAR(255) NOT NULL,
    birth_registration_no VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    religion ENUM('islam', 'hinduism', 'buddhism', 'christianity', 'other') NOT NULL,
    ethnicity VARCHAR(100) NULL,

    -- Father Information
    father_name_en VARCHAR(255) NULL,
    father_name_bn VARCHAR(255) NULL,
    father_nid VARCHAR(20) NULL,
    father_phone VARCHAR(20) NULL,
    father_occupation VARCHAR(255) NULL,

    -- Mother Information
    mother_name_en VARCHAR(255) NULL,
    mother_name_bn VARCHAR(255) NULL,
    mother_nid VARCHAR(20) NULL,
    mother_phone VARCHAR(20) NULL,
    mother_occupation VARCHAR(255) NULL,

    -- Guardian Information
    guardian_name_en VARCHAR(255) NULL,
    guardian_name_bn VARCHAR(255) NULL,
    who_guardian ENUM('whoguard1', 'whoguard2', 'whoguard3') DEFAULT 'whoguard1',

    -- Address
    present_village_en VARCHAR(255) NOT NULL,
    present_district_en VARCHAR(100) NOT NULL,
    permanent_village_en VARCHAR(255) NOT NULL,
    permanent_district_en VARCHAR(100) NOT NULL,

    -- Contact
    primary_phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,

    -- Documents
    photo_path VARCHAR(255) NULL,
    birth_certificate_path VARCHAR(255) NULL,
    academic_transcript_path VARCHAR(255) NULL,

    -- Status
    status ENUM('pending_review', 'under_review', 'accepted', 'rejected') DEFAULT 'pending_review',
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    accepted_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_student_id BIGINT UNSIGNED NULL,

    -- Signature Tracking
    principal_signed BOOLEAN DEFAULT FALSE,
    principal_signed_at TIMESTAMP NULL,
    principal_signature_path VARCHAR(255) NULL,
    class_teacher_signed BOOLEAN DEFAULT FALSE,
    class_teacher_signed_at TIMESTAMP NULL,
    class_teacher_id BIGINT UNSIGNED NULL,
    class_teacher_signature_path VARCHAR(255) NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (applying_class_id) REFERENCES classes(id),
    FOREIGN KEY (applying_section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_student_id) REFERENCES students(id) ON DELETE SET NULL,
    FOREIGN KEY (class_teacher_id) REFERENCES users(id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_tracking_code (tracking_code),
    INDEX idx_status (status),
    INDEX idx_academic_year (academic_year_id),
    INDEX idx_applying_class (applying_class_id),
    INDEX idx_birth_registration (birth_registration_no)
) ENGINE=InnoDB;
```

### fee_packages

```sql
CREATE TABLE fee_packages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    name_bn VARCHAR(255) NOT NULL,
    description TEXT NULL,
    package_type ENUM('new_admission', 'monthly_regular', 'annual_regular', 'custom') NOT NULL,
    academic_year_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    INDEX idx_package_type (package_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;
```

### fee_package_items

```sql
CREATE TABLE fee_package_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fee_package_id BIGINT UNSIGNED NOT NULL,
    fee_type_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1,
    amount DECIMAL(10, 2) NOT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (fee_package_id) REFERENCES fee_packages(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id),
    INDEX idx_package (fee_package_id)
) ENGINE=InnoDB;
```

### application_form_fees (Optional)

```sql
CREATE TABLE application_form_fees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form_number VARCHAR(50) UNIQUE NOT NULL,
    applicant_name VARCHAR(255) NULL,
    applicant_phone VARCHAR(20) NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking') DEFAULT 'cash',
    payment_date DATE NOT NULL,
    collected_by BIGINT UNSIGNED NOT NULL,
    form_issued BOOLEAN DEFAULT FALSE,
    form_submitted BOOLEAN DEFAULT FALSE,
    admission_application_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (collected_by) REFERENCES users(id),
    FOREIGN KEY (admission_application_id) REFERENCES admission_applications(id) ON DELETE SET NULL,
    INDEX idx_form_number (form_number),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB;
```

### payment_transactions

```sql
CREATE TABLE payment_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(50) UNIQUE NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking', 'cheque') DEFAULT 'cash',
    payment_date DATE NOT NULL,
    reference_number VARCHAR(100) NULL,
    collected_by BIGINT UNSIGNED NOT NULL,
    fee_package_id BIGINT UNSIGNED NULL,
    receipt_generated BOOLEAN DEFAULT FALSE,
    receipt_path VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (collected_by) REFERENCES users(id),
    FOREIGN KEY (fee_package_id) REFERENCES fee_packages(id) ON DELETE SET NULL,
    INDEX idx_transaction_number (transaction_number),
    INDEX idx_payment_date (payment_date),
    INDEX idx_student (student_id)
) ENGINE=InnoDB;
```

---

## Relationships Summary

### One-to-Many (1:*)

1. **users → profiles** (1:1 actually)
2. **classes → sections** (1:*)
3. **academic_years → admission_applications** (1:*)
4. **academic_years → enrollments** (1:*)
5. **academic_years → fee_structures** (1:*)
6. **classes → admission_applications** (applying_class) (1:*)
7. **sections → admission_applications** (applying_section) (1:*)
8. **students → enrollments** (1:*)
9. **fee_types → fee_structures** (1:*)
10. **fee_types → student_fees** (1:*)
11. **students → student_fees** (1:*)
12. **students → payment_transactions** (1:*)
13. **payment_transactions → payment_line_items** (1:*)
14. **expense_categories → expenses** (1:*)
15. **users → admission_applications** (reviewed_by) (1:*)
16. **users → admission_applications** (class_teacher_id) (1:*)
17. **users → incomes** (recorded_by) (1:*)
18. **users → expenses** (recorded_by, approved_by) (1:*)
19. **users → payment_transactions** (collected_by) (1:*)
20. **fee_packages → fee_package_items** (1:*)
21. **fee_packages → payment_transactions** (1:*)

### Many-to-Many (*:*)

1. **students ↔ guardians** (via student_guardian pivot)

### One-to-One (1:1)

1. **admission_applications ↔ students** (created_student_id)
2. **students → guardians** (primary_guardian_id)

---

## Indexes

### Critical Indexes for Performance

```sql
-- Admission Applications
CREATE INDEX idx_admission_status ON admission_applications(status);
CREATE INDEX idx_admission_tracking ON admission_applications(tracking_code);
CREATE INDEX idx_admission_year ON admission_applications(academic_year_id);
CREATE INDEX idx_admission_birth_reg ON admission_applications(birth_registration_no);

-- Students
CREATE UNIQUE INDEX idx_student_id ON students(student_id);
CREATE UNIQUE INDEX idx_birth_registration ON students(birth_registration_no);
CREATE INDEX idx_student_status ON students(status);
CREATE INDEX idx_student_class ON students(admission_class_id);

-- Enrollments
CREATE INDEX idx_enrollment_student ON enrollments(student_id);
CREATE INDEX idx_enrollment_year ON enrollments(academic_year_id);
CREATE INDEX idx_enrollment_class ON enrollments(class_id);
CREATE INDEX idx_enrollment_status ON enrollments(status);

-- Student Fees
CREATE INDEX idx_student_fees_student ON student_fees(student_id);
CREATE INDEX idx_student_fees_status ON student_fees(status);
CREATE INDEX idx_student_fees_due_date ON student_fees(due_date);
CREATE INDEX idx_student_fees_type ON student_fees(fee_type_id);

-- Payment Transactions
CREATE UNIQUE INDEX idx_transaction_number ON payment_transactions(transaction_number);
CREATE INDEX idx_payment_student ON payment_transactions(student_id);
CREATE INDEX idx_payment_date ON payment_transactions(payment_date);
CREATE INDEX idx_payment_method ON payment_transactions(payment_method);

-- Fee Packages
CREATE INDEX idx_package_type ON fee_packages(package_type);
CREATE INDEX idx_package_active ON fee_packages(is_active);
CREATE INDEX idx_package_year ON fee_packages(academic_year_id);
```

---

## Key Workflows

### 1. Admission Approval → Student Creation

```
admission_applications (approved)
         │
         ├──> students (create with student_id)
         │
         ├──> guardians (create if new)
         │
         ├──> student_guardian (pivot, link relations)
         │
         ├──> enrollments (create for current year)
         │
         └──> student_fees (initialize pending fees)
```

### 2. Fee Payment

```
payment_transactions (create)
         │
         ├──> payment_line_items (itemize fees)
         │
         └──> student_fees (update paid_amount, status)
```

### 3. Using Fee Package

```
fee_packages
         │
         ├──> fee_package_items (get items)
         │
         └──> payment_transactions (fee_package_id set)
                 │
                 └──> payment_line_items
                         │
                         └──> student_fees (update)
```

---

## Enumerations

### Student Status
```sql
ENUM('active', 'withdrawn', 'graduated', 'transferred', 'suspended')
```

### Enrollment Status
```sql
ENUM('active', 'promoted', 'detained', 'withdrawn')
```

### Application Status
```sql
ENUM('pending_review', 'under_review', 'accepted', 'rejected')
```

### Fee Status
```sql
ENUM('pending', 'partially_paid', 'paid', 'overdue', 'waived')
```

### Payment Method
```sql
ENUM('cash', 'bank_transfer', 'mobile_banking', 'cheque')
```

### Fee Package Type
```sql
ENUM('new_admission', 'monthly_regular', 'annual_regular', 'custom')
```

### Guardian Relation
```sql
ENUM('father', 'mother', 'legal_guardian', 'uncle', 'aunt',
     'grandfather', 'grandmother', 'brother', 'sister', 'other')
```

### Gender
```sql
ENUM('male', 'female', 'other')
```

### Religion
```sql
ENUM('islam', 'hinduism', 'buddhism', 'christianity', 'other')
```

### Who Guardian
```sql
ENUM('whoguard1', 'whoguard2', 'whoguard3')
-- whoguard1 = Father
-- whoguard2 = Mother
-- whoguard3 = Other Guardian
```

---

## Storage Estimates

### Typical School (500 students)

| Table | Rows/Year | Storage/Row | Total |
|-------|-----------|-------------|-------|
| students | 100 new | 2 KB | 200 KB/year |
| guardians | 200 new | 1 KB | 200 KB/year |
| enrollments | 500 | 500 B | 250 KB/year |
| student_fees | 6,000 | 200 B | 1.2 MB/year |
| payment_transactions | 6,000 | 500 B | 3 MB/year |
| payment_line_items | 12,000 | 200 B | 2.4 MB/year |
| admission_applications | 150 | 3 KB | 450 KB/year |
| fee_packages | 10 | 500 B | 5 KB |
| fee_package_items | 50 | 200 B | 10 KB |

**Total:** ~7.5 MB/year (excluding files/documents)

**Files (photos, PDFs):** ~500 MB/year

**10-year estimate:** ~5 GB total database + files

---

## Backup Strategy

### Daily Backups
- Full database dump
- Incremental file backups
- Retention: 30 days

### Weekly Backups
- Full database + files
- Retention: 12 weeks

### Monthly Backups
- Archive to cold storage
- Retention: 7 years (regulatory compliance)

---

## Key Changes from Version 1.0

1. **Removed** `applicant_fees` table (no longer needed with approve-first workflow)
2. **Removed** `payment_type` enum and `applicant_id` from `payment_transactions`
3. **Added** signature tracking fields to `admission_applications`
4. **Added** `fee_packages` and `fee_package_items` tables
5. **Added** `application_form_fees` table (optional)
6. **Updated** `payment_transactions` to include `fee_package_id`
7. **Updated** application status enum to remove `pending_payment` state
8. **Simplified** payment workflow to always link to students

---

**End of Document**
