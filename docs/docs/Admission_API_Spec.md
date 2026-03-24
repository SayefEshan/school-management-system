# Admission API Specification (Public + Admin)

This document defines the request/response contracts and validation for the Admission module. The website (public) and admin dashboard will both submit the same payload shape. Reference fields are in docs/admission_formsubmission.json.

## Request Shape (JSON, multipart for files)

Top-level object with nested groups:
- studentInformation
- fatherInformation
- motherInformation
- guardianInformation
- presentAddress
- permanentAddress
- contactInformation
- documents
- termsAccepted
- submissionMetadata

Notes:
- For public submissions, the server will overwrite `submissionMetadata.ipAddress`, `submissionMetadata.userAgent`, and `submissionMetadata.applicationTime` using server-side values.
- File fields (studentPhoto, academicTranscript, birthCertificate) must be sent as multipart/form-data. If base64 is used from the website, the backend will still store via FileManagerService.

## Example (abbreviated)

**Note:** Student English name will be automatically converted to UPPERCASE (e.g., "Md. Abdul Karim" becomes "MD. ABDUL KARIM").

**Example 1: Guardian is Father (whoguard1) - Father and Mother names required**
```json
{
  "studentInformation": {
    "studentNameBangla": "মোঃ আবদুল করিম",
    "studentNameEnglish": "Md. Abdul Karim",
    "birthRegistrationNumber": "12345678901234567",
    "dateOfBirth": "2010-05-15",
    "studentPhoto": "<file or base64>",
    "religion": "rel1",
    "gender": "gen1",
    "ethnicity": "cust1",
    "applyingForClass": "ষষ্ঠ",
    "previousClass": "পঞ্চম",
    "previousResult": "জিপিএ ৫.০০"
  },
  "fatherInformation": {
    "fatherNameBangla": "আবদুল হামিদ",
    "fatherNameEnglish": "Abdul Hamid",
    "fatherNID": "1234567890",
    "fatherPhone": "01712345678",
    "fatherOccupation": "3"
  },
  "motherInformation": {
    "motherNameBangla": "রোকেয়া বেগম",
    "motherNameEnglish": "Rokeya Begum",
    "motherNID": "9876543210",
    "motherPhone": "01812345678",
    "motherOccupation": "1"
  },
  "guardianInformation": {
    "whoGuardian": "whoguard1"
  },
  "presentAddress": { /* ... */ },
  "permanentAddress": { "sameAsPresent": true, /* ... */ },
  "contactInformation": { "primaryPhone": "01712345678", "email": "foo@bar.com" },
  "documents": { "academicTranscript": "<file>", "birthCertificate": "<file>" },
  "termsAccepted": true,
  "submissionMetadata": { "locale": "bn" }
}
```

**Example 2: Guardian is Other (whoguard3) - Guardian names required, Father/Mother names optional**
```json
{
  "studentInformation": {
    "studentNameBangla": "সাকিব হাসান",
    "studentNameEnglish": "Sakib Hasan",
    "birthRegistrationNumber": "98765432109876543",
    "dateOfBirth": "2011-08-20",
    "studentPhoto": "<file or base64>",
    "religion": "rel1",
    "gender": "gen1",
    "ethnicity": "cust1",
    "applyingForClass": "ষষ্ঠ",
    "previousClass": "পঞ্চম",
    "previousResult": "জিপিএ ৪.৮০"
  },
  "fatherInformation": {
    "fatherNameBangla": null,
    "fatherNameEnglish": null
  },
  "motherInformation": {
    "motherNameBangla": null,
    "motherNameEnglish": null
  },
  "guardianInformation": {
    "whoGuardian": "whoguard3",
    "guardianName": "মোহাম্মদ আলী",
    "guardianNameEnglish": "Mohammad Ali",
    "guardianNID": "1234567890123",
    "guardianPhone": "01712345678",
    "guardianOccupation": "3",
    "guardianRelation": "5",
    "guardianYearlyIncome": "500000"
  },
  "presentAddress": { /* ... */ },
  "permanentAddress": { "sameAsPresent": true, /* ... */ },
  "contactInformation": { "primaryPhone": "01712345678", "email": "guardian@example.com" },
  "documents": { "academicTranscript": "<file>", "birthCertificate": "<file>" },
  "termsAccepted": true,
  "submissionMetadata": { "locale": "bn" }
}
```

## Validation (Laravel-friendly)

Student
- studentInformation.studentNameBangla: required|string|max:255
- studentInformation.studentNameEnglish: required|string|max:255 (automatically converted to UPPERCASE)
- studentInformation.birthRegistrationNumber: required|regex:/^\d{17}$/
- studentInformation.dateOfBirth: required|date|before:today
- studentInformation.studentPhoto: required|image|max:50
- studentInformation.religion: required|in:rel1,rel2,rel3,rel4
- studentInformation.gender: required|in:gen1,gen2,gen3
- studentInformation.ethnicity: required|in:cust1,cust2
- studentInformation.applyingForClass: required|string
- studentInformation.previousClass: required|string
- studentInformation.previousResult: required|string|max:50

Father (required unless guardian is 'Other')
- fatherInformation.fatherNameBangla: required_unless:guardianInformation.whoGuardian,whoguard3|string|max:255
- fatherInformation.fatherNameEnglish: required_unless:guardianInformation.whoGuardian,whoguard3|string|max:255
- fatherInformation.fatherNID: nullable|regex:/^(\d{10}|\d{13}|\d{17})$/
- fatherInformation.fatherPhone: nullable|regex:/^01[3-9]\d{8}$/
- fatherInformation.fatherOccupation: nullable|in:1,2,3,4,5,6,7

Mother (required unless guardian is 'Other')
- motherInformation.motherNameBangla: required_unless:guardianInformation.whoGuardian,whoguard3|string|max:255
- motherInformation.motherNameEnglish: required_unless:guardianInformation.whoGuardian,whoguard3|string|max:255
- motherInformation.motherNID: nullable|regex:/^(\d{10}|\d{13}|\d{17})$/
- motherInformation.motherPhone: nullable|regex:/^01[3-9]\d{8}$/
- motherInformation.motherOccupation: nullable|in:1,2,3,4

Guardian (conditional - names required when whoGuardian=whoguard3)
- guardianInformation.whoGuardian: required|in:whoguard1,whoguard2,whoguard3
- guardianInformation.guardianName: required_if:guardianInformation.whoGuardian,whoguard3|string|max:255
- guardianInformation.guardianNameEnglish: required_if:guardianInformation.whoGuardian,whoguard3|string|max:255
- guardianInformation.guardianNID: required_if:guardianInformation.whoGuardian,whoguard3|regex:/^(\d{10}|\d{13}|\d{17})$/
- guardianInformation.guardianPhone: required_if:guardianInformation.whoGuardian,whoguard3|regex:/^01[3-9]\d{8}$/
- guardianInformation.guardianOccupation: required_if:guardianInformation.whoGuardian,whoguard3|in:1,2,3,4,5,6,7,8
- guardianInformation.guardianRelation: required_if:guardianInformation.whoGuardian,whoguard3|in:1,2,3,4,5,6,7,8,9,10,11,12,13,14,15
- guardianInformation.guardianYearlyIncome: required_if:guardianInformation.whoGuardian,whoguard3|string|max:50

Addresses
- presentAddress.village|postOffice|policeStation|upazila|district: required|string|max:255
- permanentAddress.sameAsPresent: required|boolean
- permanentAddress.village|postOffice|policeStation|upazila|district: required|string|max:255 (server may auto-fill when sameAsPresent=true)

Contact & Documents
- contactInformation.primaryPhone: required|regex:/^01[3-9]\d{8}$/
- contactInformation.email: nullable|email|max:255
- documents.academicTranscript: required|mimes:jpg,jpeg,png,pdf|max:200
- documents.birthCertificate: required|mimes:jpg,jpeg,png,pdf|max:200

Other
- termsAccepted: required|accepted

## Processing Rules
- Normalize: trim strings; standardize phone format; coerce `sameAsPresent` true → copy present → permanent
- Student English name: automatically convert to UPPERCASE before validation and storage
- Conditional validation: Father/Mother names required by default, but become optional when guardian is 'Other' (whoguard3)
- Files: store via FileManagerService on public disk; return relative paths; expose URLs when needed
- Locale: use submissionMetadata.locale (bn/en) when set; otherwise detect from Accept-Language
- Server metadata: always capture IP/user-agent/time server-side and store on the application

## Response Shapes
- Success (201):
```json
{ "success": true, "applicationId": "<tracking_code>", "message": "Application submitted successfully" }
```
- Validation Error (422):
```json
{ "success": false, "errors": { "field": ["message"] } }
```

Status lookup (GET /api/admissions/{tracking_code}):
```json
{ "tracking_code": "...", "status": "submitted|under_review|accepted|rejected", "submitted_at": "ISO8601" }
```

## Database Mapping

admission_applications
- id (PK)
- tracking_code (unique)
- status (enum: submitted, under_review, accepted, rejected)
- academic_year_id (FK)
- class_id (FK)
- section_id (nullable FK)
- student (JSON) — studentInformation raw
- father (JSON) — fatherInformation raw
- mother (JSON) — motherInformation raw
- guardian (JSON) — guardianInformation raw
- present_address (JSON)
- permanent_address (JSON)
- contact (JSON)
- documents (JSON) — stored paths
- submitted_ip, submitted_user_agent
- reviewed_by (nullable FK), reviewed_at (nullable TS)
- accepted_at (nullable TS), rejected_at (nullable TS), rejection_reason (nullable)
- created_at, updated_at

On Accept → create:
- students
  - id, student_code, name_bn, name_en, dob, gender, religion, ethnicity, birth_registration_number, photo_path, address_bn, address_en, primary_phone, email, guardian_id (nullable)
- guardians (0–2 rows)
  - name_bn, name_en, relation (father/mother/other), nid, phone, occupation, address_bn/en
- enrollments
  - student_id, academic_year_id, class_id, section_id, roll_no, status=active

Primary guardian
- If whoGuardian=whoguard1/2 → link father/mother as Student.guardian_id
- If whoGuardian=whoguard3 → create guardian row and link

## Admin vs Public
- Public endpoints: POST /api/admissions, GET /api/admissions/{tracking_code}
- Admin endpoints: list/view/accept/reject under /api/admin/admissions/* with auth + permissions
- Admin dashboard form can call the same POST /api/admissions payload shape (internal UI), or an admin-specific endpoint that accepts identical body plus internal flags

## Enumerations (to finalize)
- religion codes: rel1..rel4 → config/admission.php or DB lookup
- gender codes: gen1..gen3
- ethnicity/caste: cust1..cust2
- occupations: father/mother/guardian codebooks (1..N)
- guardian relations: 1..15

## Rate Limiting & CORS
- Public POST /api/admissions → throttle (e.g., 20/min per IP) and CORS allowlist for website domains

## i18n
- Place translations in resources/lang/{en,bn}/admission.php and per-module resources/lang/{en,bn}
- Return localized validation and response messages

## Testing Checklist
- Submit valid payload (with files) → 201 + tracking_code
- Submit invalid (phone/NID/BRN) → 422 localized
- sameAsPresent handling copies addresses
- Status lookup returns correct state
- Accept creates Student+Enrollment, sets primary guardian correctly
- Reject stores reason and audits
