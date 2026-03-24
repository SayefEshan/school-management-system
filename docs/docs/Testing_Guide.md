# School Management System - Complete Testing Guide

This guide provides step-by-step instructions to test the complete workflow of the School Management System, from admission application to fee collection.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [System Setup](#system-setup)
3. [Test Workflow Overview](#test-workflow-overview)
4. [Step 1: Create Academic Year](#step-1-create-academic-year)
5. [Step 2: Create Classes & Sections](#step-2-create-classes--sections)
6. [Step 3: Create & Assign Subjects](#step-3-create--assign-subjects)
7. [Step 4: Setup Fee Structure](#step-4-setup-fee-structure)
8. [Step 5: Submit Admission Application](#step-5-submit-admission-application)
9. [Step 6: Review & Approve Application](#step-6-review--approve-application)
10. [Step 7: View Student Record](#step-7-view-student-record)
11. [Step 8: Assign Student to Section](#step-8-assign-student-to-section)
12. [Step 9: Check Due Fees](#step-9-check-due-fees)
13. [Step 10: Collect Payment](#step-10-collect-payment)
14. [Step 11: Generate Reports](#step-11-generate-reports)
15. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Setup
- Laravel application running (`php artisan serve`)
- Database migrated and seeded
- User account with admin permissions
- Browser (Chrome, Firefox, or Safari recommended)

### Login Credentials
```
URL: http://localhost:8000
Email: admin@example.com
Password: password
```

---

## System Setup

### 1. Fresh Installation
```bash
# Reset database (if needed)
php artisan migrate:fresh

# Seed default data
php artisan db:seed

# Seed permissions
php artisan db:seed --class=PermissionSeeder

# Clear caches
php artisan optimize:clear
```

### 2. Verify Module Status
```bash
php artisan module:list
```

**Expected Output:**
All modules should show `Enabled` status, especially:
- Admission
- Student
- ClassManagement
- FeeCollection
- RolePermission

---

## Test Workflow Overview

```
Admission Application → Approval → Student Creation → Section Assignment → Fee Generation → Payment Collection
```

**Complete Flow:**
1. Setup Academic Year
2. Create Classes & Sections
3. Create & Assign Subjects
4. Setup Fee Structure (Fee Types & Packages)
5. Submit Admission Application (Public/Admin)
6. Admin Reviews & Approves Application
7. System Auto-Creates Student Record
8. Assign Student to Specific Section
9. View Due Fees
10. Collect Fee Payment
11. Generate Reports & Receipts

---

## Step 1: Create Academic Year

**Purpose:** Set up the academic year for all operations.

### Navigation
`Dashboard → Settings → Academic Years`

### Actions
1. Click **"Add Academic Year"**
2. Fill in details:
   - **Name (English):** `2025-2026`
   - **Name (Bangla):** `২০২৫-২০২৬` (optional)
   - **Start Date:** `2025-01-01`
   - **End Date:** `2025-12-31`
   - **Is Current:** ✅ Checked
   - **Is Active:** ✅ Checked
3. Click **"Save"**

### Expected Result
✅ Academic year created and marked as current
✅ Success message displayed
✅ Year appears in academic years list

### Verification
```bash
# Via Tinker
php artisan tinker
>>> \Modules\Admission\Models\AcademicYear::current()->first()
```

---

## Step 2: Create Classes & Sections

**Purpose:** Set up class structure for student enrollment.

### 2.1 Create Classes

**Navigation:** `Dashboard → Class Management → Classes → Add New Class`

#### Create Multiple Classes

**Class 1:**
- **Name (English):** `Class 6`
- **Name (Bangla):** `ষষ্ঠ শ্রেণী`
- **Order:** `6`
- **Active:** ✅ Checked
- Click **"Create Class"**

**Class 2:**
- **Name (English):** `Class 7`
- **Name (Bangla):** `সপ্তম শ্রেণী`
- **Order:** `7`
- **Active:** ✅ Checked
- Click **"Create Class"**

**Class 3:**
- **Name (English):** `Class 8`
- **Name (Bangla):** `অষ্টম শ্রেণী`
- **Order:** `8`
- **Active:** ✅ Checked
- Click **"Create Class"**

### 2.2 Create Sections for Each Class

**Navigation:** `Class Management → Classes → [Select Class] → Manage Sections`

#### For Class 6:

1. Click **"Add Section"** button
2. Fill section details:

   **Section A:**
   - **Name:** `A`
   - **Name (Bangla):** `ক`
   - **Capacity:** `40`
   - **Active:** ✅ Checked

   **Section B:**
   - **Name:** `B`
   - **Name (Bangla):** `খ`
   - **Capacity:** `40`
   - **Active:** ✅ Checked

3. Click **"Save Sections"**

#### Repeat for Class 7 and Class 8

### Expected Result
✅ Classes created and visible in dashboard
✅ Sections created with capacity limits
✅ Class Management dashboard shows:
   - Total Classes: 3
   - Total Sections: 6
   - Active Classes: 3

### Verification
Check Class Management Dashboard at: `/classmanagement`

---

## Step 3: Create & Assign Subjects

**Purpose:** Define subjects and assign them to classes.

### 3.1 Create Subjects

**Navigation:** `Class Management → Subjects → Add New Subject`

#### Create Core Subjects

**Subject 1: Mathematics**
- **Subject Code:** `MATH`
- **Name (English):** `Mathematics`
- **Name (Bangla):** `গণিত`
- **Type:** `Core`
- **Active:** ✅ Checked
- Click **"Create Subject"**

**Subject 2: English**
- **Subject Code:** `ENG`
- **Name (English):** `English`
- **Name (Bangla):** `ইংরেজি`
- **Type:** `Core`
- **Active:** ✅ Checked

**Subject 3: Bangla**
- **Subject Code:** `BAN`
- **Name (English):** `Bangla`
- **Name (Bangla):** `বাংলা`
- **Type:** `Core`
- **Active:** ✅ Checked

**Subject 4: Science**
- **Subject Code:** `SCI`
- **Name (English):** `Science`
- **Name (Bangla):** `বিজ্ঞান`
- **Type:** `Core`
- **Active:** ✅ Checked

### 3.2 Assign Subjects to Classes

**Navigation:** `Class Management → Classes → [Select Class 6] → Assign Subjects`

1. Select **Academic Year:** `2025-2026`
2. Check all subjects:
   - ✅ Mathematics (Mandatory: ✅)
   - ✅ English (Mandatory: ✅)
   - ✅ Bangla (Mandatory: ✅)
   - ✅ Science (Mandatory: ✅)
3. Click **"Save Subject Assignments"**

**Repeat for Class 7 and Class 8**

### Expected Result
✅ Subjects created and listed
✅ Subjects assigned to all classes
✅ Dashboard shows subject counts per class

---

## Step 4: Setup Fee Structure

**Purpose:** Configure fee types and packages for payment collection.

### 4.1 Seed Fee Types

```bash
php artisan module:seed --class=FeeTypeSeeder FeeCollection
```

**This creates 14 standard Bangladesh school fee types:**
- Admission Fee (ভর্তি ফি)
- Tuition Fee (শিক্ষা ফি)
- Monthly Fee (মাসিক ফি)
- Exam Fee (পরীক্ষা ফি)
- Library Fee (গ্রন্থাগার ফি)
- Lab Fee (ল্যাব ফি)
- Sports Fee (খেলাধুলা ফি)
- Development Fee (উন্নয়ন ফি)
- ID Card Fee (পরিচয়পত্র ফি)
- Transport Fee (যাতায়াত ফি)
- Session Fee (সেশন চার্জ)
- Late Fee (বিলম্ব ফি)
- Certificate Fee (সার্টিফিকেট ফি)
- Prospectus Fee (প্রসপেক্টাস ফি)

### 4.2 Create Fee Package (Optional)

**Navigation:** `Fee Collection → Fee Packages → Add New Package`

**Example: Class 6 Admission Package**
- **Name:** `Class 6 - Admission Package`
- **Name (Bangla):** `ষষ্ঠ শ্রেণী - ভর্তি প্যাকেজ`
- **Class:** `Class 6`
- **Academic Year:** `2025-2026`
- **Active:** ✅ Checked

**Add Fee Items:**
1. Admission Fee - ৳ 1,000
2. Tuition Fee - ৳ 500
3. ID Card Fee - ৳ 100
4. Development Fee - ৳ 300

**Total Package Amount:** ৳ 1,900

Click **"Create Package"**

### Expected Result
✅ Fee types seeded successfully
✅ Fee package created (optional)
✅ Package visible in fee packages list

---

## Step 5: Submit Admission Application

**Purpose:** Test the online admission application system.

### 5.1 Via Public API (Recommended for Testing)

**Endpoint:** `POST /api/admissions`

**Using Postman/Thunder Client:**

```json
{
  "academic_year_id": 1,
  "applying_class_id": 1,
  "student_name_en": "Rahul Ahmed",
  "student_name_bn": "রাহুল আহমেদ",
  "date_of_birth": "2013-05-15",
  "gender": "male",
  "blood_group": "B+",
  "religion": "islam",
  "nationality": "bangladeshi",
  "present_address_en": "123 Main Street, Dhaka",
  "present_address_bn": "১২৩ মেইন স্ট্রিট, ঢাকা",
  "permanent_address_en": "123 Main Street, Dhaka",
  "permanent_address_bn": "১২৩ মেইন স্ট্রিট, ঢাকা",
  "father_name_en": "Karim Ahmed",
  "father_name_bn": "করিম আহমেদ",
  "father_occupation": "Business",
  "father_phone": "01712345678",
  "mother_name_en": "Fatema Ahmed",
  "mother_name_bn": "ফাতেমা আহমেদ",
  "mother_occupation": "Housewife",
  "mother_phone": "01712345679",
  "guardian_phone": "01712345678",
  "guardian_email": "karim.ahmed@example.com"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Application submitted successfully",
  "data": {
    "application_id": 1,
    "tracking_code": "ADM-2025-000001",
    "status": "pending"
  }
}
```

### 5.2 Via Admin Panel

**Navigation:** `Admission → Applications → Add New Application`

Fill the same details as above and submit.

### Expected Result
✅ Application created with status "pending"
✅ Tracking code generated (format: ADM-YYYY-XXXXXX)
✅ Application visible in applications list
✅ Email notification sent to guardian (if configured)

### Track Application
**Endpoint:** `GET /api/admissions/{tracking_code}`

```bash
curl http://localhost:8000/api/admissions/ADM-2025-000001
```

---

## Step 6: Review & Approve Application

**Purpose:** Admin reviews and approves the admission application.

### Navigation
`Dashboard → Admission → Applications`

### Review Application

1. Find the application in the list
2. Click **"View"** or **"Review"** button
3. Review all submitted information:
   - Student details
   - Guardian information
   - Documents (if uploaded)
   - Address details

### Approve Application

1. Click **"Approve"** or **"Accept"** button
2. **Optional:** Select section during approval (if available)
3. Confirm approval

### Expected Result - CRITICAL!
✅ Application status changed to "approved"
✅ **Student record automatically created**
✅ **Student ID generated** (Format: YYCCSSSSS)
   - Example: `2506000001` (Year: 25, Class: 06, Serial: 00001)
✅ **Enrollment record created** (status: enrolled)
✅ **Fees initialized** as pending
✅ Success notification displayed

### Verification

```bash
php artisan tinker

# Check student created
>>> \Modules\Student\Models\Student::latest()->first()

# Verify student ID format
>>> $student = \Modules\Student\Models\Student::latest()->first()
>>> $student->student_id
=> "2506000001"

# Check enrollment
>>> $student->enrollments()->first()

# Check fee initialization
>>> \Modules\FeeCollection\Models\StudentFee::where('student_id', $student->id)->get()
```

### What Happens During Approval?

**Automatic Processes:**

1. **Student Creation:**
   - Name (English & Bangla)
   - Date of Birth
   - Gender, Blood Group, Religion
   - Contact Information
   - Student ID generated

2. **Enrollment Creation:**
   - Links student to class
   - Links to academic year
   - Status: 'enrolled'
   - No section assigned yet (assigned later)

3. **Fee Initialization:**
   - Creates fee records with status 'pending'
   - Based on class fee structure
   - Awaits payment

4. **Guardian Relationship:**
   - Father details saved
   - Mother details saved
   - Guardian contact information

---

## Step 7: View Student Record

**Purpose:** Verify student record creation and view details.

### Navigation
`Dashboard → Students → Student List`

### Actions

1. **Search for Student:**
   - Use search bar
   - Enter student name: `Rahul Ahmed`
   - Or use Student ID: `2506000001`

2. **View Student Profile:**
   - Click on student name
   - Review all details

### Expected Student Profile Display

**Personal Information:**
- Student ID: `2506000001`
- Name: `Rahul Ahmed` / `রাহুল আহমেদ`
- Date of Birth: `15 May 2013`
- Age: `11 years`
- Gender: `Male`
- Blood Group: `B+`
- Religion: `Islam`

**Academic Information:**
- Academic Year: `2025-2026`
- Class: `Class 6`
- Section: `Not Assigned` (will assign next)
- Roll Number: `Not Assigned`
- Status: `Enrolled`

**Guardian Information:**
- Father: `Karim Ahmed` / `করিম আহমেদ`
- Father Phone: `01712345678`
- Mother: `Fatema Ahmed` / `ফাতেমা আহমেদ`
- Guardian Email: `karim.ahmed@example.com`

**Address:**
- Present: `123 Main Street, Dhaka`
- Permanent: `123 Main Street, Dhaka`

### Expected Result
✅ Student record visible and complete
✅ All information correctly displayed
✅ Enrollment status shows "enrolled"
✅ Section and Roll Number are "Not Assigned"

---

## Step 8: Assign Student to Section

**Purpose:** Assign the student to a specific section within their class.

### Method 1: From Student Profile

**Navigation:** `Students → [Select Student] → Edit`

1. Click **"Edit Student"**
2. Find **"Section Assignment"** section
3. **Select Section:** `A`
4. System auto-generates **Roll Number** (e.g., `001`)
5. Click **"Update Student"**

### Method 2: From Class Management

**Navigation:** `Class Management → Classes → [Class 6] → Manage Sections → [Section A]`

1. View section student list
2. Click **"Assign Student"**
3. Search and select student: `Rahul Ahmed`
4. System assigns next available roll number
5. Click **"Assign"**

### Roll Number Generation Logic

**Rules:**
- Sequential per section (001, 002, 003...)
- Unique within section
- Auto-generated on assignment
- Cannot be manually changed after assignment

**Examples:**
- Class 6, Section A, Roll 001
- Class 6, Section A, Roll 002
- Class 6, Section B, Roll 001 (separate sequence)

### Expected Result
✅ Student assigned to Section A
✅ Roll Number auto-generated: `001`
✅ Section capacity updated (39/40 remaining)
✅ Student profile shows:
   - Section: `A`
   - Roll Number: `001`

### Verification

```bash
php artisan tinker

>>> $student = \Modules\Student\Models\Student::where('student_id', '2506000001')->first()
>>> $enrollment = $student->enrollments()->first()
>>> $enrollment->section_id
=> 1
>>> $enrollment->roll_number
=> 1
```

---

## Step 9: Check Due Fees

**Purpose:** View pending fees for the student.

### Navigation
`Students → [Select Student] → Fees Tab`

OR

`Fee Collection → Student Fees → Search Student`

### View Fee Summary

**Expected Display:**

**Student:** Rahul Ahmed (2506000001)
**Class:** Class 6, Section A

| Fee Type | Amount (৳) | Due Date | Status |
|----------|------------|----------|--------|
| Admission Fee | 1,000 | 2025-01-15 | Pending |
| Tuition Fee | 500 | 2025-01-31 | Pending |
| Monthly Fee | 300 | 2025-01-31 | Pending |
| ID Card Fee | 100 | 2025-01-15 | Pending |
| Development Fee | 300 | 2025-01-15 | Pending |

**Total Due:** ৳ 2,200
**Total Paid:** ৳ 0
**Balance:** ৳ 2,200

### Fee Status Indicators

- 🟡 **Pending** - Not paid, not overdue
- 🔴 **Overdue** - Past due date, not paid
- 🟢 **Paid** - Fully paid
- 🔵 **Partial** - Partially paid

### Expected Result
✅ All fees displayed correctly
✅ Status shows "Pending"
✅ Total balance calculated correctly
✅ "Pay Now" button visible

---

## Step 10: Collect Payment

**Purpose:** Process fee payment for the student.

### Navigation
`Fee Collection → Collect Payment`

OR

`Students → [Select Student] → Fees → Pay Now`

### 10.1 Select Payment Items

**Step 1: Student Selection**
1. Search student by:
   - Student ID: `2506000001`
   - OR Name: `Rahul Ahmed`
   - OR Class & Section
2. Click **"Select Student"**

**Step 2: Select Fees to Pay**

**Option A: Use Fee Package**
- Select package: `Class 6 - Admission Package`
- All package items auto-selected
- Total: ৳ 1,900

**Option B: Select Individual Fees**
- ✅ Admission Fee - ৳ 1,000
- ✅ Tuition Fee - ৳ 500
- ✅ ID Card Fee - ৳ 100
- ✅ Development Fee - ৳ 300
- **Selected Total:** ৳ 1,900

### 10.2 Payment Details

**Fill Payment Information:**
- **Payment Date:** `2025-01-10` (today)
- **Payment Method:** `Cash` / `Bank Transfer` / `Mobile Banking`
- **Reference Number:** `TXN123456` (optional)
- **Amount Paying:** ৳ 1,900 (auto-filled)
- **Discount:** ৳ 0 (optional)
- **Final Amount:** ৳ 1,900
- **Notes:** `First payment - Admission fees` (optional)

**Payment Methods Available:**
- Cash
- Bank Transfer
- Mobile Banking (bKash, Nagad, Rocket)
- Cheque

### 10.3 Process Payment

1. Review payment summary
2. Click **"Process Payment"**
3. Confirm transaction

### Expected Result
✅ Payment transaction created
✅ Receipt number generated (e.g., `RCP-2025-000001`)
✅ Selected fees marked as "Paid"
✅ Payment history updated
✅ Receipt generated (PDF)
✅ Success message displayed

### 10.4 View Receipt

**Receipt Details Display:**

```
------------------------------------------
      SCHOOL NAME
      School Address
------------------------------------------
     PAYMENT RECEIPT
------------------------------------------
Receipt No: RCP-2025-000001
Date: 10 January 2025
------------------------------------------
Student Information:
Name: Rahul Ahmed
Student ID: 2506000001
Class: Class 6, Section A
------------------------------------------
Payment Details:

Admission Fee          ৳ 1,000.00
Tuition Fee            ৳   500.00
ID Card Fee            ৳   100.00
Development Fee        ৳   300.00
                    ---------------
Total Amount           ৳ 1,900.00
Discount               ৳     0.00
                    ---------------
Amount Paid            ৳ 1,900.00
                    ===============
Payment Method: Cash
Reference: TXN123456
------------------------------------------
Received By: Admin User
Date & Time: 10 Jan 2025, 10:30 AM
------------------------------------------
Signature: _______________
------------------------------------------
```

### 10.5 Verify Payment

**Check Updated Fee Status:**

| Fee Type | Amount (৳) | Status | Paid Date |
|----------|------------|--------|-----------|
| Admission Fee | 1,000 | ✅ Paid | 10 Jan 2025 |
| Tuition Fee | 500 | ✅ Paid | 10 Jan 2025 |
| Monthly Fee | 300 | 🟡 Pending | - |
| ID Card Fee | 100 | ✅ Paid | 10 Jan 2025 |
| Development Fee | 300 | ✅ Paid | 10 Jan 2025 |

**New Balance:**
- Total Due: ৳ 300 (Monthly Fee only)
- Total Paid: ৳ 1,900
- Outstanding: ৳ 300

### Verification

```bash
php artisan tinker

# Check payment transaction
>>> $payment = \Modules\FeeCollection\Models\Payment::latest()->first()
>>> $payment->receipt_number
=> "RCP-2025-000001"
>>> $payment->amount
=> 1900.00

# Check fee status
>>> $fees = \Modules\FeeCollection\Models\StudentFee::where('student_id', 1)->get()
>>> $fees->where('status', 'paid')->count()
=> 4
```

---

## Step 11: Generate Reports

**Purpose:** Verify reporting functionality.

### 11.1 Daily Collection Report

**Navigation:** `Fee Collection → Reports → Daily Collection`

**Filter:**
- Date: `10 January 2025`
- Click **"Generate Report"**

**Expected Display:**
- Total Collection: ৳ 1,900
- Number of Transactions: 1
- Payment Methods Breakdown
- List of all payments today

**Export Options:**
- 📄 **Download PDF**
- 📊 **Export to Excel**

### 11.2 Monthly Collection Report

**Navigation:** `Fee Collection → Reports → Monthly Collection`

**Filter:**
- Month: `January 2025`
- Class: `All Classes` or `Class 6`
- Click **"Generate Report"**

**Expected Display:**
- Month-wise collection summary
- Class-wise breakdown
- Fee type breakdown
- Total revenue

### 11.3 Student Fee Statement

**Navigation:** `Students → [Select Student] → Fee Statement`

**Expected Display:**
- All fee transactions
- Payment history
- Outstanding balance
- Downloadable statement PDF

### 11.4 Fee Defaulters Report

**Navigation:** `Fee Collection → Reports → Defaulters`

**Filter:**
- Overdue Days: `> 30 days`
- Class: `All`

**Expected Display:**
- List of students with overdue fees
- Total overdue amount per student
- Last payment date
- Contact information

---

## Complete Test Checklist

### ✅ Academic Setup
- [ ] Academic Year created and set as current
- [ ] Classes created (at least 3)
- [ ] Sections created for each class (at least 2 per class)
- [ ] Subjects created (at least 4)
- [ ] Subjects assigned to classes

### ✅ Fee Structure
- [ ] Fee types seeded
- [ ] Fee package created (optional)
- [ ] Fee amounts configured

### ✅ Admission Process
- [ ] Application submitted successfully
- [ ] Tracking code generated
- [ ] Application visible in admin panel
- [ ] Application can be tracked via API

### ✅ Student Creation (Auto on Approval)
- [ ] Application approved
- [ ] Student record created automatically
- [ ] Student ID generated correctly (YYCCSSSSS format)
- [ ] Enrollment record created
- [ ] Fees initialized as pending

### ✅ Student Management
- [ ] Student visible in student list
- [ ] Student profile displays all information
- [ ] Student assigned to section
- [ ] Roll number auto-generated
- [ ] Section capacity updated

### ✅ Fee Collection
- [ ] Due fees visible
- [ ] Payment can be created
- [ ] Multiple fees can be paid together
- [ ] Fee package can be used
- [ ] Receipt generated with unique number
- [ ] Fee status updated to "Paid"
- [ ] Payment history recorded

### ✅ Reporting
- [ ] Daily collection report generated
- [ ] Monthly collection report generated
- [ ] Student fee statement accessible
- [ ] Defaulters report generated
- [ ] Reports exportable to PDF/Excel

### ✅ Dashboard Statistics
- [ ] Class Management dashboard shows correct counts
- [ ] Student module shows enrollment statistics
- [ ] Fee Collection dashboard shows revenue

---

## Troubleshooting

### Issue 1: Application Approval Fails

**Symptoms:** Error when approving application

**Solutions:**
```bash
# Check current academic year exists
php artisan tinker
>>> \Modules\Admission\Models\AcademicYear::current()->first()

# Check class exists
>>> \Modules\Admission\Models\ClassModel::find(1)

# Clear caches
php artisan optimize:clear
```

### Issue 2: Student ID Not Generated

**Symptoms:** Student created but no student_id

**Solutions:**
```bash
# Check StudentService generateStudentId method
# Verify class has 'order' field set
php artisan tinker
>>> $class = \Modules\Admission\Models\ClassModel::find(1)
>>> $class->order

# Manually regenerate if needed
>>> $student = \Modules\Student\Models\Student::find(1)
>>> $service = app(\Modules\Student\Services\StudentService::class)
>>> $studentId = $service->generateStudentId($student->enrollments->first()->class_id)
```

### Issue 3: Section Assignment Fails

**Symptoms:** Cannot assign student to section

**Solutions:**
- Check section has available capacity
- Verify student has active enrollment
- Ensure student not already assigned to another section in same class

```bash
php artisan tinker
>>> $section = \Modules\Admission\Models\Section::find(1)
>>> $section->current_enrollment_count
>>> $section->capacity
```

### Issue 4: Fees Not Showing

**Symptoms:** No fees displayed for student

**Solutions:**
```bash
# Check fee types exist
php artisan tinker
>>> \Modules\FeeCollection\Models\FeeType::count()

# Seed if missing
php artisan module:seed --class=FeeTypeSeeder FeeCollection

# Check student fees initialized
>>> \Modules\FeeCollection\Models\StudentFee::where('student_id', 1)->count()
```

### Issue 5: Payment Processing Fails

**Symptoms:** Error when collecting payment

**Solutions:**
- Verify fee IDs are valid
- Check payment amount matches selected fees
- Ensure student exists and is enrolled

```bash
# Check fee details
php artisan tinker
>>> $fee = \Modules\FeeCollection\Models\StudentFee::find(1)
>>> $fee->amount
>>> $fee->status
```

### Issue 6: Dashboard Statistics Incorrect

**Symptoms:** Counts don't match actual data

**Solutions:**
```bash
# Clear all caches
php artisan optimize:clear

# Verify queries
php artisan tinker
>>> \Modules\Admission\Models\ClassModel::count()
>>> \Modules\Admission\Models\Section::count()
>>> \Modules\Student\Models\Enrollment::where('status', 'enrolled')->count()
```

---

## Advanced Testing Scenarios

### Scenario 1: Multiple Students - Same Class

1. Create 5 admission applications for Class 6
2. Approve all applications
3. Assign to different sections (3 in Section A, 2 in Section B)
4. Verify roll numbers are sequential per section
5. Collect fees for all students
6. Generate class-wise collection report

### Scenario 2: Student Promotion

1. Enroll student in Class 6 for 2025-2026
2. Create new academic year 2026-2027
3. Promote student to Class 7
4. Verify enrollment history maintained
5. Generate new fees for Class 7
6. Check student has multiple enrollment records

### Scenario 3: Partial Payment

1. Select multiple fees (total ৳ 2,000)
2. Pay only ৳ 1,000
3. Verify system creates partial payment
4. Check remaining balance is ৳ 1,000
5. Complete payment in second transaction
6. Verify both transactions recorded

### Scenario 4: Fee Waiver/Discount

1. Select fees for payment
2. Apply discount (e.g., 10%)
3. Process payment with discounted amount
4. Verify discount recorded in payment
5. Check fee status updated correctly

### Scenario 5: Bulk Operations

1. Approve 10 applications in batch
2. Verify all students created with unique IDs
3. Bulk assign to sections
4. Generate class-wise fee report

---

## Performance Testing

### Load Test Parameters

**Test with:**
- 100 students
- 10 classes with 3 sections each
- 50 payment transactions per day
- 20 concurrent admission applications

**Monitor:**
- Page load times
- Database query performance
- Receipt generation speed
- Report generation time

---

## Security Testing

### Authentication & Authorization

1. **Test without login:**
   - Try accessing admin pages
   - Expected: Redirect to login

2. **Test with user without permissions:**
   - Login as user without "Approve Admission" permission
   - Try to approve application
   - Expected: Access denied

3. **Test API endpoints:**
   - Try accessing protected endpoints without token
   - Expected: 401 Unauthorized

### Data Validation

1. **Negative amount in payment**
   - Expected: Validation error

2. **Invalid date format**
   - Expected: Validation error

3. **Duplicate student ID**
   - Expected: System prevents (auto-generated)

---

## Test Data Summary

### Users
- Admin: `admin@example.com` / `password`

### Academic Year
- 2025-2026 (Current)

### Classes
- Class 6, Class 7, Class 8

### Sections (per class)
- Section A (Capacity: 40)
- Section B (Capacity: 40)

### Subjects
- Mathematics (MATH)
- English (ENG)
- Bangla (BAN)
- Science (SCI)

### Test Student
- Name: Rahul Ahmed
- Student ID: 2506000001
- Class: 6, Section: A, Roll: 001

### Fee Package
- Class 6 Admission Package: ৳ 1,900

---

## Success Criteria

The system passes testing if:

✅ All admission applications can be submitted and tracked
✅ Admin can approve applications
✅ Students are created automatically with correct IDs
✅ Students can be assigned to sections with auto-generated roll numbers
✅ Fees are correctly initialized and displayed
✅ Payments can be processed with receipt generation
✅ All reports generate correctly
✅ Dashboard statistics are accurate
✅ No errors in console/logs during normal operations
✅ Mobile responsive UI works correctly
✅ Bilingual content displays properly

---

## Next Steps After Testing

1. **User Acceptance Testing (UAT)**
   - Have school staff test the complete workflow
   - Gather feedback on UI/UX
   - Document any edge cases found

2. **Performance Optimization**
   - Profile slow queries
   - Add database indexes if needed
   - Optimize report generation

3. **Additional Features**
   - SMS notifications
   - Email notifications
   - Mobile app integration
   - Attendance module
   - Exam module

4. **Production Deployment**
   - Setup production environment
   - Configure email/SMS gateways
   - Setup automated backups
   - Configure SSL certificate
   - Setup monitoring and logging

---

## Support & Documentation

- **Full Documentation:** `/docs/` directory
- **API Documentation:** `docs/Admission_API_Spec.md`
- **Database Schema:** `docs/Database_ERD.md`
- **Workflow Diagrams:** `docs/Complete_Workflow_Diagrams.md`
- **Development Guide:** `CLAUDE.md`

---

**Document Version:** 1.0
**Last Updated:** December 2024
**Tested On:** Laravel 12, PHP 8.2
