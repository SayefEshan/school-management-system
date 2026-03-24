# Admission & Student Modules — Implementation Plan

This document outlines the incremental implementation for two modules: Admission and Student. The SRS files in docs are references only; we will build these modules step-by-step.

Scope (Phase-by-Phase)
- Phase 1: Admission Management
  - Public API to submit applications and check status
  - Admin review workflow: list, view, accept, reject
  - Audit logs for state transitions
  - i18n (English/Bangla) for validation and API messages
- Phase 2: Student Info Management
  - Convert accepted applications to Student + initial Enrollment
  - Student listing and detail APIs (admin)
  - Minimal CRUD for student profile updates

Modules and Responsibilities
- Admission module
  - Public: submit admissions, query status
  - Admin: review queue, accept/reject
  - Service layer handles business logic and transactions
  - Emits domain events (Submitted/Accepted/Rejected)
- Student module
  - Student profile and guardian linkage
  - Enrollment (Academic Year/Class/Section)
  - Service layer for creation and updates
  - Emits domain events (StudentCreated/EnrollmentCreated)

Dependencies and Integration
- RolePermission: seed new permissions (see Permissions section)
- ActivityLog: audit submission and status transitions; student/enrollment creation
- Settings: store and retrieve current Academic Year; toggle admissions if needed
- FileManagerService: handle uploaded attachments

Data Model (initial)
Note: Adapt to existing tables if already present.
- academic_years
  - id, name, start_date, end_date, is_current
- classes
  - id, name, name_bn, order
- sections
  - id, class_id, name, name_bn
- admission_applications
  - id, tracking_code (unique), status [submitted, under_review, accepted, rejected]
  - applicant fields: first_name, last_name, first_name_bn, last_name_bn, dob, gender, phone, email
  - target fields: academic_year_id, class_id, section_id (nullable)
  - previous_school, address_en, address_bn
  - attachments (json), submitted_ip, submitted_user_agent
  - reviewed_by, reviewed_at, accepted_at, rejected_at, rejection_reason
- students
  - id, student_code (generated), first_name, last_name, first_name_bn, last_name_bn, dob, gender, phone, email, address_en, address_bn, guardian_id (nullable), photo_path
- guardians
  - id, name, name_bn, relation, phone, email, address_en, address_bn, occupation
- enrollments
  - id, student_id, academic_year_id, class_id, section_id, roll_no, status [active, promoted, withdrawn]

Public API (Admission)
- POST /api/admissions
  - Body: applicant + target info, optional Bengali fields, attachments[] (files or URLs)
  - Response: 201 { tracking_code, message }
  - Validation: strict, localized errors; size/type checks on attachments
  - Protections: throttle (e.g., 20/min), optional CAPTCHA hook, CORS allowlist
- GET /api/admissions/{tracking_code}
  - Response: { status, submitted_at, target: { year, class, section }, message }

Admin API (Admission)
- GET /api/admin/admissions?status=&class_id=&year=
- GET /api/admin/admissions/{id}
- POST /api/admin/admissions/{id}/accept
  - Transaction: create Student + Enrollment, mark application accepted, audit, emit events
- POST /api/admin/admissions/{id}/reject
  - Body: { rejection_reason }

Admin API (Student)
- GET /api/admin/students?class_id=&year=
- GET /api/admin/students/{id}
- POST /api/admin/students
- PATCH /api/admin/students/{id}

Permissions (seed via RolePermission)
- Admissions: View Admissions, Review Admissions, Accept Admission, Reject Admission
- Students: View Student, Create Student, Edit Student
- Assign to roles (e.g., Admin, Admissions Officer) as appropriate

Localization (English/Bangla)
- Place translations in resources/lang/en and resources/lang/bn at app level and inside each module under resources/lang/{en,bn}
- Use a locale middleware to set app locale from Accept-Language header or ?locale=bn; default fallback en
- Return localized validation messages and API texts via Lang::get
- Data: store parallel bn fields where supplied (names/addresses)

Events and Auditing
- Domain events: AdmissionSubmitted, AdmissionAccepted, AdmissionRejected, StudentCreated, EnrollmentCreated
- ActivityLog captures: who/when, IP, user-agent, old/new values on transitions

File Uploads
- Use FileManagerService for all attachment handling
- Allowed types: images/PDF (configurable); max size per .env/config
- Store under public disk with generated paths; return URLs in responses when appropriate

Validation and Security
- Input validation via Form Request classes (separate public vs admin rules)
- Throttling for public endpoints; optional CAPTCHA integration hook
- CORS settings for allowed origins (website submitting admissions)

Testing Plan
- Feature tests (Admission): submit valid/invalid, status lookup, throttle
- Feature tests (Admin Admission): list, view, accept (creates Student+Enrollment), reject (stores reason), permissions
- Feature tests (Student): list, view, create/edit minimal
- Unit tests: AdmissionService (accept/reject), StudentService (code/roll generation), localization

Milestones & Acceptance Criteria
- M1: Schema & Scaffolding
  - Modules created; migrations for academic_years, classes, sections, admission_applications, students, guardians, enrollments
  - Seeds: current year, sample classes/sections, permissions
  - ACCEPT: migrate/seed run cleanly; routes stubbed
- M2: Public Admission API
  - Endpoints for submit/status; validations; uploads; throttle; i18n messages
  - ACCEPT: 201 with tracking_code; status returns; localized errors; throttling enforced
- M3: Admin Review
  - List/view; accept (Tx: Student+Enrollment) and reject; auditing; permissions
  - ACCEPT: records created/updated correctly; logs present; permissions enforced
- M4: Student APIs
  - List/view student; minimal create/edit
  - ACCEPT: responses filtered by class/year; validations and permissions pass
- M5: Polish & Docs
  - Refactor, events wired; brief README/WARP updates; example requests added

Implementation Notes
- Keep business logic in Services; controllers thin
- Wrap accept/reject in DB transactions
- Use config/modules.php generators for consistent module structure
- Respect existing naming and app conventions (queues, auditing, helpers)

See also
- docs/Admission_API_Spec.md (request/response contracts, validations, DB mapping)
- docs/admission_formsubmission.json (source field list from website)
