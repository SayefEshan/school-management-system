# Complete Workflow Diagrams
## End-to-End Process Flows for School Management System

**Version:** 2.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

This document contains comprehensive workflow diagrams for all major processes in the school management system, using the **Approve-First, Pay-Later** approach with signature tracking support.

---

## 1. ADMISSION TO STUDENT WORKFLOW

### Complete End-to-End Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ PHASE 1: APPLICATION SUBMISSION (Public Portal)                │
└─────────────────────────────────────────────────────────────────┘

    [Start]
       │
       ▼
    Applicant fills online admission form
    ├─ Student information (Bengali + English)
    ├─ Father/Mother/Guardian information
    ├─ Select Class & Section
    ├─ Upload documents (photo, certificates)
    └─ Submit application
       │
       ▼
    System validates form
    ├─ Check required fields
    ├─ Validate file sizes
    ├─ Check section capacity
    └─ Validate birth registration (unique)
       │
       ├─ [Invalid] ────> Show errors, return to form
       │
       ▼ [Valid]
    Create admission_applications record
    ├─ status: pending_review
    ├─ Generate tracking_code: ADM-2025-000123
    └─ Store all form data
       │
       ▼
    Display confirmation
    ├─ Tracking Code: ADM-2025-000123
    ├─ Message: "Application submitted for review"
    └─ Next Steps: "Wait for approval notification"


┌─────────────────────────────────────────────────────────────────┐
│ PHASE 2: ADMIN REVIEW & APPROVAL (Admin Dashboard)             │
└─────────────────────────────────────────────────────────────────┘

    [Admin logs into dashboard]
       │
       ▼
    View pending applications
    ├─ Filter: status = pending_review OR submitted
    ├─ Sort: by submission date
    └─ Display application list
       │
       ▼
    Admin opens application
    ├─ View all student information
    ├─ View parent/guardian info
    ├─ View uploaded documents
    ├─ Check class/section availability
    └─ Check signature status (if applicable)
       │
       ▼
    Admin makes decision
       │
       ├────> [REJECT]
       │         │
       │         ▼
       │      Mark as under_review (optional)
       │         │
       │         ▼
       │      Reject with reason
       │      ├─ Update status: rejected
       │      ├─ Enter rejection_reason
       │      ├─ rejected_at: timestamp
       │      └─ Send notification to parent
       │         │
       │         ▼
       │      [END: Application Rejected]
       │
       └────> [APPROVE]
                │
                ▼
             [Continue to Phase 3]


┌─────────────────────────────────────────────────────────────────┐
│ PHASE 2B: SIGNATURE WORKFLOW (Optional - Can occur before/after)│
└─────────────────────────────────────────────────────────────────┘

    [Signature can be collected at any time]
       │
       ├──> BEFORE APPROVAL
       │    └─ Admin marks signature status during review
       │
       └──> AFTER APPROVAL (more common)
            └─ Admin updates signature after student created
       │
       ▼
    Update Application Signatures
    ├─ Principal Signature
    │  ├─ principal_signed: true/false
    │  ├─ principal_signed_at: timestamp
    │  └─ principal_signature_path: (optional digital signature)
    │
    └─ Class Teacher Signature
       ├─ class_teacher_signed: true/false
       ├─ class_teacher_signed_at: timestamp
       ├─ class_teacher_id: user_id
       └─ class_teacher_signature_path: (optional)
       │
       ▼
    Generate admission form with signatures
    └─ PDF includes signature spaces/stamps


┌─────────────────────────────────────────────────────────────────┐
│ PHASE 3: STUDENT CREATION (Automatic on Accept)                │
└─────────────────────────────────────────────────────────────────┘

    [Admin clicks "Accept Application"]
       │
       ▼
    BEGIN DATABASE TRANSACTION
       │
       ▼
    STEP 1: Generate Student ID
    ├─ Get admission year: 2025 → 25
    ├─ Get class code: Class 6 → 06
    ├─ Get next serial: Query last student in 2506* → 00015
    └─ Student ID: 2506000015
       │
       ▼
    STEP 2: Create Student Record
    ├─ student_id: 2506000015
    ├─ name_en: MOHAMMAD RAHIM (uppercase)
    ├─ name_bn: মোহাম্মদ রহিম
    ├─ date_of_birth: from application
    ├─ birth_registration_no: from application
    ├─ gender, religion, ethnicity: from application
    ├─ phone, email: from application
    ├─ addresses (present & permanent): from application
    ├─ photo_path: from application
    ├─ admission_year: 2025
    ├─ admission_class_id: Class 6
    ├─ admission_application_id: link
    └─ status: active
       │
       ▼
    STEP 3: Create Guardian(s)
       │
       ├─> IF father info exists:
       │   └─ Create guardians record
       │       ├─ name_en, name_bn, nid, phone, occupation
       │       └─ Create student_guardian pivot
       │           ├─ relation: father
       │           ├─ is_primary: (if whoGuardian = whoguard1)
       │           └─ is_emergency_contact: true
       │
       ├─> IF mother info exists:
       │   └─ Create guardians record
       │       ├─ name_en, name_bn, nid, phone, occupation
       │       └─ Create student_guardian pivot
       │           ├─ relation: mother
       │           ├─ is_primary: (if whoGuardian = whoguard2)
       │           └─ is_emergency_contact: true
       │
       └─> IF other guardian (whoGuardian = whoguard3):
           └─ Create guardians record
               ├─ name_en, name_bn, nid, phone
               ├─ occupation, yearly_income, relation
               └─ Create student_guardian pivot
                   ├─ relation: legal_guardian
                   ├─ is_primary: true
                   └─ is_emergency_contact: true
       │
       ▼
    STEP 4: Set Primary Guardian
    └─ Update students.primary_guardian_id
       │
       ▼
    STEP 5: Create Enrollment
    ├─ student_id: 2506000015
    ├─ academic_year_id: 2025
    ├─ class_id: Class 6
    ├─ section_id: from application (Section A)
    ├─ roll_no: Generate next available in Class 6
    │   └─ Query: MAX(roll_no) WHERE class_id=6 AND status=active
    │   └─ Result: 22, Assign: 23
    ├─ status: active
    └─ enrollment_date: today
       │
       ▼
    STEP 6: Initialize Student Fees (Pending Payment)
    └─ Get fee_structures for Class 6, Year 2025
        │
        ├─> One-Time Fees (admission year)
        │   └─ Create student_fees records
        │       ├─ Admission Fee: ৳5,000
        │       ├─ Session Fee: ৳3,000
        │       ├─ Badge/Sash: ৳500
        │       ├─ Prospectus: ৳300
        │       ├─ Development Fee: ৳2,000
        │       ├─ paid_amount: 0
        │       ├─ due_amount: amount
        │       └─ status: pending
        │
        ├─> Monthly Tuition (12 months)
        │   └─ Create 12 student_fees records
        │       ├─ month: 1, 2, 3... 12
        │       ├─ amount: 2,000 each
        │       ├─ paid_amount: 0
        │       ├─ due_amount: 2,000
        │       ├─ due_date: 5th of each month
        │       └─ status: pending
        │
        └─> Exam Fees (per term)
            └─ Create student_fees for upcoming exams
       │
       ▼
    STEP 7: Update Admission Application
    ├─ status: accepted
    ├─ reviewed_by: admin user_id
    ├─ reviewed_at: timestamp
    ├─ accepted_at: timestamp
    └─ created_student_id: 2506000015
       │
       ▼
    STEP 8: Trigger Events
    ├─> StudentCreated event
    ├─> StudentEnrolled event
    └─> Send notification (email/SMS to parent)
        └─ "Application approved! Student ID: 2506000015"
        └─ "Please visit school to complete fee payment"
       │
       ▼
    COMMIT TRANSACTION
       │
       ▼
    Show success message
    ├─ Student created: 2506000015
    ├─ Roll number assigned: 23
    ├─ Fees initialized (pending payment)
    └─ [View Student Profile] button
       │
       ▼
    [Student now active - awaiting fee payment]


┌─────────────────────────────────────────────────────────────────┐
│ PHASE 4: FEE PAYMENT COLLECTION (School Office)                │
└─────────────────────────────────────────────────────────────────┘

    [Parent visits school after approval notification]
       │
       ▼
    Staff searches student
    ├─ By student ID: 2506000015
    ├─ By tracking code: ADM-2025-000123
    └─ By name: Mohammad Rahim
       │
       ▼
    Display student information
    ├─ Student ID: 2506000015
    ├─ Name: Mohammad Rahim
    ├─ Class: 6, Section: A, Roll: 23
    └─ Status: Active (fees pending)
       │
       ▼
    Display pending fees dashboard
    ┌─────────────────────────────────────────┐
    │ PENDING FEES:                           │
    │ ○ Admission Fee        ৳5,000           │
    │ ○ Session Fee          ৳3,000           │
    │ ○ January Tuition      ৳2,000           │
    │ ○ Badge/Sash           ৳500             │
    │ ○ Prospectus           ৳300             │
    │ ○ Development Fee      ৳2,000           │
    ├─────────────────────────────────────────┤
    │ Total Pending:         ৳12,800          │
    └─────────────────────────────────────────┘
       │
       ▼
    Staff selects payment method
       │
       ├───> OPTION A: Use Fee Package
       │     │
       │     ▼
       │  Select "New Student Admission Package 2025"
       │  ├─ Auto-selects all admission fees
       │  ├─ Total: ৳10,800
       │  └─ One-click selection
       │     │
       │     ▼
       │  [Continue to payment]
       │
       └───> OPTION B: Select Individual Fees
             │
             ▼
          Manual fee selection
          ├─ ☑ Admission Fee       ৳5,000
          ├─ ☑ Session Fee         ৳3,000
          ├─ ☑ January Tuition     ৳2,000
          ├─ ☑ Badge/Sash          ৳500
          └─ Total: ৳10,500
             │
             ▼
          [Continue to payment]
       │
       ▼
    Enter payment details
    ├─ Payment method: Cash / Bank Transfer / Mobile Banking
    ├─ Reference number (if applicable)
    └─ Notes
       │
       ▼
    BEGIN DATABASE TRANSACTION
       │
       ├─> Generate transaction number
       │   └─ PAY-2025-000045
       │
       ├─> Create payment_transactions
       │   ├─ transaction_number: PAY-2025-000045
       │   ├─ student_id: 2506000015
       │   ├─ total_amount: 10,800
       │   ├─ payment_method: cash
       │   ├─ fee_package_id: (if package used)
       │   └─ collected_by: staff user_id
       │
       ├─> Create payment_line_items (4-5 rows)
       │   ├─ Admission Fee: ৳5,000
       │   ├─ Session Fee: ৳3,000
       │   ├─ January Tuition: ৳2,000
       │   ├─ Badge/Sash: ৳500
       │   └─ (Optional) Prospectus: ৳300
       │
       ├─> Update student_fees records
       │   ├─ Admission Fee:
       │   │   ├─ paid_amount: 5,000
       │   │   ├─ due_amount: 0
       │   │   └─ status: paid
       │   │
       │   ├─ Session Fee:
       │   │   ├─ paid_amount: 3,000
       │   │   ├─ due_amount: 0
       │   │   └─ status: paid
       │   │
       │   └─ (Continue for all paid items...)
       │
       └─> Generate receipt PDF
           ├─ Receipt #: PAY-2025-000045
           ├─ Student info + itemized list
           └─ Package name (if used)
       │
       ▼
    COMMIT TRANSACTION
       │
       ▼
    Print receipt for parent
       │
       ▼
    [Payment recorded successfully]


┌─────────────────────────────────────────────────────────────────┐
│ PHASE 4B: OPTIONAL APPLICATION FORM FEE (Before Submission)    │
└─────────────────────────────────────────────────────────────────┘

    [Some schools charge fee just to get the form]
       │
       ▼
    Prospective parent visits school
    └─ Wants to purchase application form
       │
       ▼
    Staff collects form fee
    ├─ Amount: ৳200
    ├─ Payment method: cash
    └─ Applicant info (name, phone - minimal)
       │
       ▼
    BEGIN TRANSACTION
       │
       ├─> Generate form number
       │   └─ FORM-2025-00123
       │
       ├─> Create application_form_fees record
       │   ├─ form_number: FORM-2025-00123
       │   ├─ applicant_name: (basic info)
       │   ├─ applicant_phone: (basic info)
       │   ├─ amount_paid: 200
       │   ├─ payment_method: cash
       │   ├─ collected_by: staff user_id
       │   └─ form_issued: true
       │
       └─> Generate mini receipt
           └─ Form #: FORM-2025-00123
       │
       ▼
    COMMIT TRANSACTION
       │
       ▼
    Issue blank application form
       │
       ▼
    [Parent fills form at home]
       │
       ▼
    [Parent submits online with form number]
       │
       ▼
    System links form fee to application
    └─ application_form_fees.admission_application_id = new app


┌─────────────────────────────────────────────────────────────────┐
│ PHASE 5: ONGOING FEE PAYMENTS (Monthly)                        │
└─────────────────────────────────────────────────────────────────┘

    [Parent visits for monthly tuition]
       │
       ▼
    Staff searches student
    ├─ By student ID: 2506000015
    ├─ By name: Mohammad Rahim
    └─ By class/roll: 6-A / Roll 23
       │
       ▼
    Display student fee dashboard
    ┌─────────────────────────────────────────┐
    │ PAID FEES:                              │
    │ ✓ Admission Fee        ৳5,000           │
    │ ✓ Session Fee          ৳3,000           │
    │ ✓ January Tuition      ৳2,000           │
    ├─────────────────────────────────────────┤
    │ DUE FEES:                               │
    │ ○ February Tuition     ৳2,000 (Due now) │
    │ ○ March Tuition        ৳2,000           │
    │ ○ April Exam Fee       ৳800             │
    ├─────────────────────────────────────────┤
    │ Total Outstanding:     ৳4,800           │
    └─────────────────────────────────────────┘
       │
       ▼
    Staff selects payment method
       │
       ├───> OPTION A: Use Monthly Package
       │     └─ "Monthly Regular Package"
       │        ├─ Tuition: ৳2,000
       │        ├─ Tiffin: ৳500
       │        └─ Total: ৳2,500
       │
       └───> OPTION B: Select Individual Fees
             ├─ ☑ February Tuition    ৳2,000
             └─ ☑ March Tuition       ৳2,000 (advance)
                └─ Total: ৳4,000
       │
       ▼
    BEGIN TRANSACTION
       │
       ├─> Create payment_transactions
       │   ├─ student_id: 2506000015
       │   ├─ total_amount: 4,000
       │   ├─ fee_package_id: (if package used)
       │   └─ payment_method: cash
       │
       ├─> Create payment_line_items (2 rows)
       │   ├─ February Tuition: ৳2,000
       │   └─ March Tuition: ৳2,000
       │
       ├─> Update student_fees
       │   ├─ February fee:
       │   │   ├─ paid_amount: 2,000
       │   │   ├─ due_amount: 0
       │   │   └─ status: paid
       │   │
       │   └─ March fee:
       │       ├─ paid_amount: 2,000
       │       ├─ due_amount: 0
       │       └─ status: paid
       │
       └─> Generate receipt
           └─ Receipt #: PAY-2025-000234
       │
       ▼
    COMMIT TRANSACTION
       │
       ▼
    Print receipt for parent
       │
       ▼
    [Payment recorded successfully]
```

---

## 2. STATE DIAGRAM: ADMISSION APPLICATION

```
┌─────────────────────────────────────────────────────────────────┐
│ ADMISSION APPLICATION STATUS STATES (Approve-First Model)       │
└─────────────────────────────────────────────────────────────────┘

                          [Form Submitted]
                                │
                                ▼
                        ┌──────────────────┐
                        │  pending_review  │◄────┐
                        └──────────────────┘     │
                         │              │        │
                  [Admin Reviews]        │        │
                         │              │        │
                         ▼              │        │
                ┌──────────────────┐   │        │
                │  under_review    │───┘        │
                └──────────────────┘            │
                         │                      │
           [Admin Decision]                     │
                    │                           │
         ┌──────────┴──────────┐                │
         │                     │                │
         ▼                     ▼                │
  ┌──────────────┐      ┌──────────────┐       │
  │   rejected   │      │   accepted   │       │
  └──────────────┘      └──────────────┘       │
    [END STATE]              │                 │
                             │                 │
                    [Student Created]          │
                             │                 │
                             ▼                 │
                  [Application Completed]      │
                                               │
  Note: Application can return from           │
  under_review to pending_review if more      │
  documents/info needed ─────────────────────┘

  Signature Status (parallel tracking):
  ┌───────────────────────────────────┐
  │ • principal_signed: true/false    │
  │ • class_teacher_signed: true/false│
  │ Can be updated at any stage       │
  └───────────────────────────────────┘
```

---

## 3. STUDENT STATUS LIFECYCLE

```
┌─────────────────────────────────────────────────────────────────┐
│ STUDENT STATUS TRANSITIONS                                      │
└─────────────────────────────────────────────────────────────────┘

              [Created from Accepted Admission]
                            │
                            ▼
                    ┌──────────────┐
             ┌──────│    active    │──────┐
             │      └──────────────┘      │
             │              │             │
             │              │             │
  [Disciplinary Issue]  [Leaves School] [Completes Class 10]
             │              │             │
             ▼              ▼             ▼
      ┌────────────┐  ┌────────────┐  ┌───────────┐
      │ suspended  │  │ withdrawn  │  │ graduated │
      └────────────┘  └────────────┘  └───────────┘
             │              │             │
    [End of Suspension]     │             │
             │              │             │
             └───────┬──────┘             │
                     │                    │
           [Can be reactivated]    [FINAL STATE]
                     │
                     ▼
                ┌─────────────┐
                │transferred  │
                └─────────────┘
                     │
              [To another school]
                     │
                     ▼
               [FINAL STATE]
```

---

## 4. ENROLLMENT STATUS PER YEAR

```
┌─────────────────────────────────────────────────────────────────┐
│ ENROLLMENT STATUS (Per Academic Year)                           │
└─────────────────────────────────────────────────────────────────┘

         [Student Enrolled in Class 6, Year 2025]
                            │
                            ▼
                    ┌──────────────┐
                    │    active    │
                    └──────────────┘
                            │
                            │
                    [End of Academic Year]
                            │
                    [Based on Performance]
                            │
         ┌──────────────────┼──────────────────┐
         │                  │                  │
         ▼                  ▼                  ▼
  ┌────────────┐    ┌────────────┐    ┌────────────┐
  │  promoted  │    │  detained  │    │ withdrawn  │
  └────────────┘    └────────────┘    └────────────┘
         │                  │                  │
         │                  │                  │
         ▼                  ▼                  │
  [New enrollment]   [Repeat same      [END of enrollment]
   in Class 7,        class next
   Year 2026]         year]
         │                  │
         └──────────┬───────┘
                    │
         [Creates new enrollment record]
                    │
                    ▼
            [Year 2026 enrollment]
```

---

## 5. FEE PAYMENT WORKFLOW

```
┌─────────────────────────────────────────────────────────────────┐
│ FEE LIFECYCLE                                                    │
└─────────────────────────────────────────────────────────────────┘

      [Student Enrolled] OR [Fee Structure Updated]
                    │
                    ▼
        ┌────────────────────────┐
        │ Assign Fees to Student │
        │ (student_fees created) │
        └────────────────────────┘
                    │
                    ▼
            ┌──────────────┐
            │   pending    │
            └──────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
        ▼                       ▼
  [Partial Payment]      [Full Payment]
        │                       │
        ▼                       ▼
┌──────────────────┐    ┌──────────────┐
│ partially_paid   │    │     paid     │
└──────────────────┘    └──────────────┘
        │                    │
        │                [FINAL STATE]
        │
   [More payments]
        │
        ├─[Full]──────────> paid
        │
        └─[Past Due Date]
                │
                ▼
        ┌──────────────┐
        │   overdue    │
        └──────────────┘
                │
        ┌───────┴────────┐
        │                │
        ▼                ▼
  [Payment Made]  [Fee Waived]
        │                │
        ▼                ▼
    [paid]          ┌────────┐
                    │ waived │
                    └────────┘
                        │
                   [FINAL STATE]
```

---

## 6. PAYMENT TRANSACTION FLOW (With Package Support)

```
┌─────────────────────────────────────────────────────────────────┐
│ MULTI-ITEM PAYMENT TRANSACTION                                  │
└─────────────────────────────────────────────────────────────────┘

    [Staff initiates payment collection]
                    │
                    ▼
        ┌───────────────────────────┐
        │ Display unpaid fees       │
        │ Display available packages│
        └───────────────────────────┘
                    │
                    ▼
        Choose payment method
                    │
         ┌──────────┴────────────┐
         │                       │
         ▼                       ▼
   [Use Package]          [Select Individual]
         │                       │
         ▼                       ▼
   Select package:      Staff selects fees:
   ┌─────────────┐      ├─ ☑ Fee 1: ৳2,000
   │ New Student │      ├─ ☑ Fee 2: ৳800
   │ Package 2025│      └─ ☑ Fee 3: ৳1,500
   │             │          Total: ৳4,300
   │ ৳10,800     │
   └─────────────┘
         │                       │
         └───────────┬───────────┘
                     │
                     ▼
        [Enter payment details]
        ├─ Amount received
        ├─ Payment method
        ├─ Reference number (if applicable)
        └─ Notes
                    │
                    ▼
        BEGIN TRANSACTION
                    │
                    ├──> Create payment_transactions
                    │    ├─ Generate transaction_number
                    │    ├─ Store total_amount
                    │    ├─ Store payment_method
                    │    ├─ Link to student
                    │    └─ fee_package_id (if package used)
                    │
                    ├──> Create payment_line_items
                    │    ├─ Item 1: Link to fee_type_id, amount
                    │    ├─ Item 2: Link to fee_type_id, amount
                    │    └─ Item 3: Link to fee_type_id, amount
                    │
                    ├──> Update student_fees
                    │    ├─ Fee 1: paid_amount += amount
                    │    ├─ Fee 2: paid_amount += amount
                    │    ├─ Fee 3: paid_amount += amount
                    │    └─ Recalculate due_amount, update status
                    │
                    └──> Generate receipt PDF
                         ├─ Transaction number
                         ├─ Package name (if used)
                         ├─ Itemized list
                         └─ Total amount
                    │
                    ▼
        COMMIT TRANSACTION
                    │
                    ▼
        [Print receipt for parent]
                    │
                    ▼
        [Payment completed successfully]
```

---

## 7. MONTHLY FEE GENERATION (Automated)

```
┌─────────────────────────────────────────────────────────────────┐
│ AUTOMATED MONTHLY FEE ASSIGNMENT                                │
└─────────────────────────────────────────────────────────────────┘

    [Scheduled Job: 1st day of each month]
                    │
                    ▼
        Get all active students
                    │
                    ▼
        FOR EACH student:
            │
            ├──> Get current enrollment
            │    └─ academic_year, class, section
            │
            ├──> Get fee_structures for this class
            │    └─ Filter: monthly fees only
            │
            ├──> Check if already assigned for this month
            │    │
            │    └─[Not exists]──┐
            │                    │
            │                    ▼
            └──> Create student_fees
                 ├─ fee_type: Monthly Tuition
                 ├─ month: current month
                 ├─ amount: from fee_structure
                 ├─ paid_amount: 0
                 ├─ due_amount: amount
                 ├─ due_date: 5th of month
                 └─ status: pending
                    │
                    ▼
        Log: X fees created for Y students
                    │
                    ▼
        [Job completed]
```

---

## 8. OVERDUE FEE MARKING (Automated)

```
┌─────────────────────────────────────────────────────────────────┐
│ AUTOMATED OVERDUE STATUS UPDATE                                 │
└─────────────────────────────────────────────────────────────────┘

    [Scheduled Job: Daily at midnight]
                    │
                    ▼
        Get all student_fees
        WHERE status IN ('pending', 'partially_paid')
        AND due_date < CURDATE()
                    │
                    ▼
        FOR EACH overdue fee:
            │
            ├──> Update status = 'overdue'
            │
            ├──> Calculate days overdue
            │
            ├──> Apply late fee (if configured)
            │    └─ If days > 15: add ৳100 late fee
            │
            └──> Send notification
                 ├─ SMS to parent
                 └─ Email reminder
                    │
                    ▼
        Log: X fees marked overdue
                    │
                    ▼
        [Job completed]
```

---

## 9. STUDENT PROMOTION WORKFLOW

```
┌─────────────────────────────────────────────────────────────────┐
│ ANNUAL STUDENT PROMOTION                                         │
└─────────────────────────────────────────────────────────────────┘

    [End of Academic Year - Admin initiates promotion]
                    │
                    ▼
        Select students for promotion
        ├─ Filter by class: Class 6
        ├─ Filter by section: All/Specific
        ├─ Filter by performance: Passed
        └─ Bulk select: ☑ Select All
                    │
                    ▼
        Configure promotion
        ├─ From Class: 6
        ├─ To Class: 7
        ├─ Academic Year: 2026
        └─ Section: Auto-assign / Manual
                    │
                    ▼
        BEGIN TRANSACTION
                    │
                    ├──> FOR EACH selected student:
                    │    │
                    │    ├─> Update current enrollment
                    │    │   ├─ status: promoted
                    │    │   └─ promotion_date: today
                    │    │
                    │    ├─> Create new enrollment
                    │    │   ├─ student_id: same
                    │    │   ├─ academic_year_id: 2026
                    │    │   ├─ class_id: Class 7
                    │    │   ├─ section_id: assigned/null
                    │    │   ├─ roll_no: new (next available)
                    │    │   └─ status: active
                    │    │
                    │    └─> Assign new year fees
                    │        ├─ Re-admission fee (yearly)
                    │        ├─ Session fee (yearly)
                    │        ├─ Monthly tuition (12 months)
                    │        └─ Other applicable fees
                    │
                    ├──> Trigger events
                    │    └─ StudentPromoted event
                    │
                    └──> Send notifications
                         └─ Congratulations message
                    │
                    ▼
        COMMIT TRANSACTION
                    │
                    ▼
        Display summary
        ├─ X students promoted
        ├─ From Class 6 to Class 7
        └─ New roll numbers assigned
                    │
                    ▼
        [Promotion completed]
```

---

## 10. SIGNATURE COLLECTION WORKFLOW

```
┌─────────────────────────────────────────────────────────────────┐
│ ADMISSION FORM SIGNATURE TRACKING                               │
└─────────────────────────────────────────────────────────────────┘

    [Application in system (any status)]
                    │
                    ▼
        Admin opens application
                    │
                    ▼
        View signature status
        ┌────────────────────────────────┐
        │ Principal Signature: ✗ Pending │
        │ Class Teacher: ✗ Pending       │
        └────────────────────────────────┘
                    │
                    ▼
        Admin records signature
                    │
         ┌──────────┴──────────┐
         │                     │
         ▼                     ▼
   [Principal Sign]     [Class Teacher Sign]
         │                     │
         ▼                     ▼
   Update fields:        Update fields:
   ├─ principal_signed    ├─ class_teacher_signed
   ├─ principal_signed_at ├─ class_teacher_signed_at
   ├─ signed_by_user_id   ├─ class_teacher_id
   └─ signature_path      └─ signature_path
         │                     │
         └──────────┬──────────┘
                    │
                    ▼
        Generate admission form PDF
        ├─ Include signature stamps/spaces
        ├─ Principal: ✓ Signed on [date]
        └─ Class Teacher: ✓ Signed on [date]
                    │
                    ▼
        [Signatures recorded]
                    │
                    ▼
        Display updated status
        ┌────────────────────────────────┐
        │ Principal Signature: ✓ Signed  │
        │ Class Teacher: ✓ Signed        │
        └────────────────────────────────┘

Note: Signatures can be collected:
- BEFORE approval (during review)
- AFTER approval (before/after payment)
- Independently of other workflow steps
```

---

**End of Document**
