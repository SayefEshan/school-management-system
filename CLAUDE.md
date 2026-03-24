# School Management System

## Project Overview
A comprehensive school management system for Bangladeshi schools with bilingual (English/Bangla) support, built on Laravel 12 with a modular architecture.

## Development Commands
```bash
composer dev          # Run server + queue + logs + vite
php artisan serve     # Laravel dev server
npm run dev           # Vite dev server
php artisan test      # Run tests
./vendor/bin/pint     # Code formatting
```

## Architecture

### Modular Structure (`nwidart/laravel-modules`)
Each module is self-contained under `Modules/` with its own Models, Services, Controllers, Migrations, Seeders, Routes, and Tests.

**Utility Modules (Existing):**
- `RolePermission` — spatie/laravel-permission RBAC
- `ActivityLog` — owen-it/laravel-auditing audit trails
- `User` — User management & profiles
- `Settings` — System-wide configuration
- `Notification` — Email & SMS notifications
- `PushNotification` — Firebase push notifications
- `ImportDownloadManager` — Excel import/export (fast-excel)
- `BackupCleanup` — Database backup management

**Domain Modules (To Build):**
- `Admission` — Application submission, review, accept/reject + academic year/class/section management
- `Student` — Student profiles, guardians, enrollments
- `ClassManagement` — Subjects, schedules, teacher assignments, materials
- `FeeCollection` — Fee types, packages, payments, receipts, reports

### Key Dependencies
| Package | Purpose |
|---------|---------|
| `nwidart/laravel-modules` | Modular architecture |
| `spatie/laravel-permission` | Roles & permissions |
| `owen-it/laravel-auditing` | Audit trail |
| `mpdf/mpdf` | PDF generation (receipts, reports) |
| `rap2hpoutre/fast-excel` | Excel import/export |
| `laravel/sanctum` | API authentication |
| `spatie/laravel-data` | Data transfer objects |

### Implementation Patterns
- **Service Layer:** Business logic in `Services/`, controllers stay thin
- **Form Requests:** Validation in dedicated Request classes
- **DB Transactions:** Wrap multi-step operations (accept admission, collect payment)
- **Auditable Models:** Critical models implement `OwenIt\Auditing\Contracts\Auditable`
- **Bilingual Fields:** Parallel `_en` / `_bn` columns for names and addresses
- **API Responses:** Use `apiResponse()` helper for consistent JSON responses

## Critical Workflows

### Approve-First, Pay-Later
```
Application Submitted → Admin Reviews → Approved → Student Created → Fees Initialized (Pending) → Payment Collected
```
This is the core workflow. Students are NOT created until an admission application is explicitly approved by an admin.

### Student ID Format: `YYCCSSSSS`
- `YY` = Admission year (e.g., 25 for 2025)
- `CC` = Class code (e.g., 06 for Class 6)
- `SSSSS` = Sequential serial (e.g., 00001)
- Example: `2506000001` = First student admitted to Class 6 in 2025

### Fee Categories
14 standard Bangladeshi school fee types: Admission Form, Admission Fee, Tuition, Session, Exam, Registration, Form Fill-up, Badge/Sash, Prospectus/Certificate, BSC/Scout/Sports, Building/Development, Tiffin/Meal, Online Fee, Miscellaneous.

## Architecture Documentation
Detailed specifications are in `docs/`:
- `Admission_API_Spec.md` — API contracts for admission
- `Admission_Student_Implementation.md` — Phased build plan
- `Student_Module_Complete.md` — Student module spec
- `Student_ID_Generation_Logic.md` — ID generation algorithm
- `Class_Management_Module.md` — Schedules & teacher assignments
- `Payment_System_Architecture.md` — Multi-item payments & packages
- `Fee_Structure_Bangladesh.md` — 14 fee categories with amounts
- `Financial_Reports_Specification.md` — Report layouts
- `Complete_Workflow_Diagrams.md` — End-to-end process flows
- `Database_ERD.md` — Complete schema & relationships
- `Testing_Guide.md` — Manual testing walkthrough

## Database
- **Engine:** MySQL
- **Naming:** Snake case, plural tables
- **Soft Deletes:** Via `tableDataInfo()` helper on relevant tables
- **Timestamps:** All tables include `created_at`, `updated_at`
