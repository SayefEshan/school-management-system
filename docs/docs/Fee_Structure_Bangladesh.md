# Fee Structure for Bangladeshi Schools
## Complete Fee Categories and Payment Rules

**Version:** 1.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

This document defines the 14 standard fee categories used in Bangladeshi schools, their payment frequencies, typical amounts, and business rules.

---

## Fee Categories

### 1. Admission Form Fee (ভর্তি ফরম ফি)

**Code:** `ADM_FORM`
**Category:** `admission_form`
**Payment Frequency:** One-time (Pre-admission)
**Mandatory:** Yes
**Typical Amount:** ৳200 - ৳500

**Description:**
Fee paid when purchasing/submitting the admission form. This is paid by applicants BEFORE their application is reviewed.

**Business Rules:**
- Required before application can be submitted for review
- Non-refundable
- Lowest among all fees
- Same for all classes

**Payment Timing:** At application submission

---

### 2. Admission / Re-Admission Fee (ভর্তি / পুনঃভর্তি ফি)

**Code:** `ADM_FEE`
**Category:** `admission_fee`
**Payment Frequency:** One-time per academic year
**Mandatory:** Yes
**Typical Amount:** ৳3,000 - ৳10,000

**Description:**
Main admission fee for new students or re-admission fee for continuing students at the start of each academic year.

**Class-wise Amounts:**
```
Class 6:  ৳5,000
Class 7:  ৳5,500
Class 8:  ৳6,000
Class 9:  ৳7,000
Class 10: ৳8,000
```

**Business Rules:**
- New students: Pay at initial enrollment
- Continuing students: Pay at start of new academic year
- Amount varies by class
- Non-refundable

**Payment Timing:**
- New students: After application approval
- Continuing students: January-February

---

### 3. Monthly Tuition Fee (মাসিক টিউশন ফি)

**Code:** `TUITION`
**Category:** `monthly_tuition`
**Payment Frequency:** Monthly (12 times/year)
**Mandatory:** Yes
**Typical Amount:** ৳1,500 - ৳3,000/month

**Description:**
Regular monthly fee for academic instruction and classroom activities.

**Class-wise Amounts:**
```
Class 6:  ৳2,000/month = ৳24,000/year
Class 7:  ৳2,200/month = ৳26,400/year
Class 8:  ৳2,500/month = ৳30,000/year
Class 9:  ৳2,800/month = ৳33,600/year
Class 10: ৳3,000/month = ৳36,000/year
```

**Business Rules:**
- Payable by 5th of each month
- Late payment after 15th incurs ৳100 late fee
- Can pay multiple months in advance
- Covers January - December

**Payment Timing:** Monthly, due on 5th

**Calculation Example:**
```
Student enrolled: January 15, 2025
Remaining months: Jan-Dec = 12 months
Total annual tuition: 12 × ৳2,000 = ৳24,000

At enrollment: Pay January + February = ৳4,000
Monthly thereafter: ৳2,000 due by 5th of each month
```

---

### 4. Session Fee (সেশন ফি)

**Code:** `SESSION_FEE`
**Category:** `session_fee`
**Payment Frequency:** Yearly (per academic session)
**Mandatory:** Yes
**Typical Amount:** ৳2,000 - ৳5,000/year

**Description:**
Annual fee covering infrastructure, facilities, and operational costs for the academic session.

**Class-wise Amounts:**
```
Class 6:  ৳3,000
Class 7:  ৳3,200
Class 8:  ৳3,500
Class 9:  ৳4,000
Class 10: ৳4,500
```

**Business Rules:**
- Paid once per academic year
- Usually collected with admission/re-admission fee
- Covers full academic session (January-December)
- Non-refundable

**Payment Timing:** At start of academic year or enrollment

---

### 5. Exam Fee / Board Fee (পরীক্ষা ফি / বোর্ড ফি)

**Code:** `EXAM_FEE`
**Category:** `exam_fee`
**Payment Frequency:** Per exam/term
**Mandatory:** Conditional
**Typical Amount:** ৳500 - ৳5,000/exam

**Description:**
Fee for internal exams and board examinations.

**Exam Types:**
```
1st Terminal Exam (April):     ৳800
2nd Terminal Exam (August):     ৳800
Pre-Test/Model Test (November): ৳500
SSC Board Fee (Class 10 only):  ৳3,500 - ৳5,000
```

**Business Rules:**
- Internal exams: 2-3 times per year
- Board fee: Only for Class 10 SSC students
- Must be paid before exam date
- Includes question papers, answer scripts, invigilation

**Payment Timing:**
- Terminal exams: 2 weeks before exam
- Board fee: October-November (for SSC)

---

### 6. Registration Fee (নিবন্ধন ফি)

**Code:** `REGISTRATION_FEE`
**Category:** `registration_fee`
**Payment Frequency:** One-time or yearly
**Mandatory:** Conditional
**Typical Amount:** ৳500 - ৳2,000

**Description:**
Fee for student registration with education board or school registration system.

**When Applicable:**
- Class 9: Board registration (৳1,500)
- Class 10: SSC examination registration (৳2,000)

**Business Rules:**
- Separate from board exam fee
- One-time per registration period
- Required for board students only (Class 9-10)

**Payment Timing:** At start of Class 9 or before SSC

---

### 7. Form Fill-Up Fee (ফরম পূরণ ফি)

**Code:** `FORM_FILLUP`
**Category:** `form_fillup`
**Payment Frequency:** Per exam
**Mandatory:** Conditional
**Typical Amount:** ৳200 - ৳500/exam

**Description:**
Fee for filling up board examination forms.

**When Applicable:**
- Class 10: SSC form fill-up (৳300-500)
- Covers form processing and submission costs

**Business Rules:**
- Only for board exam students
- Paid when submitting exam forms
- Covers administrative processing

**Payment Timing:** Before board exam form submission

---

### 8. Badge / Sash (ব্যাজ / স্যাশ)

**Code:** `BADGE_SASH`
**Category:** `badge_sash`
**Payment Frequency:** One-time
**Mandatory:** Optional/Required
**Typical Amount:** ৳300 - ৳800

**Description:**
Cost of school uniform accessories (badge, house sash, ID card).

**Items Included:**
```
School Badge:         ৳200
House Sash:           ৳300
ID Card:              ৳150
Complete Package:     ৳500-800
```

**Business Rules:**
- Usually mandatory for new students
- Replacement available at same cost
- May be optional depending on school policy

**Payment Timing:** At enrollment or as needed

---

### 9. Prospectus / Certificate / T-Shirt / Number Slip

**Code:** `PROSPECTUS_CERT`
**Category:** `prospectus_cert`
**Payment Frequency:** As needed
**Mandatory:** Optional
**Typical Amount:** ৳100 - ৳500/item

**Description:**
Fees for various school documents and items.

**Item Breakdown:**
```
Prospectus:                    ৳200
Transfer Certificate (TC):    ৳300
Character Certificate:         ৳150
Mark Sheet Copy:               ৳100
Sports T-Shirt:                ৳400
Number Slip (for exams):       ৳50
```

**Business Rules:**
- Pay per item requested
- Certificates: At time of request
- T-Shirt: Optional, for sports participants
- Number slip: Per exam participation

**Payment Timing:** On demand

---

### 10. B.S.C / Scout / Sports (বি.এস.সি / স্কাউট / খেলাধুলা)

**Code:** `BSC_SCOUT_SPORTS`
**Category:** `bsc_scout_sports`
**Payment Frequency:** Yearly or per activity
**Mandatory:** Optional
**Typical Amount:** ৳500 - ৳2,000/year

**Description:**
Fee for co-curricular activities and clubs.

**Activity Breakdown:**
```
Bangladesh Scout:              ৳1,000/year
BNCC (Bangladesh National Cadet Corps): ৳1,200/year
Sports Club:                   ৳800/year
Cultural Club:                 ৳600/year
```

**Business Rules:**
- Optional participation
- Annual fee for registered members
- Covers activity costs, uniforms, competitions
- Can participate in multiple activities

**Payment Timing:** At activity registration

---

### 11. Building / Development Fee (ভবন / উন্নয়ন ফি)

**Code:** `BUILDING_DEV`
**Category:** `building_dev`
**Payment Frequency:** Yearly or one-time
**Mandatory:** Conditional
**Typical Amount:** ৳1,000 - ৳5,000

**Description:**
Fee for school infrastructure development and maintenance.

**Purpose:**
- Building construction/renovation
- Facility upgrades
- Infrastructure maintenance
- Equipment purchase

**Class-wise Amounts:**
```
Class 6-8:  ৳2,000/year
Class 9-10: ৳2,500/year
```

**Business Rules:**
- Usually mandatory
- Collected annually
- May be waived for economically disadvantaged students
- Transparent usage reporting to parents

**Payment Timing:** With session fee at year start

---

### 12. Tiffin / Meal Fee (টিফিন / খাবার ফি)

**Code:** `TIFFIN_MEAL`
**Category:** `tiffin_meal`
**Payment Frequency:** Monthly or term-based
**Mandatory:** Optional
**Typical Amount:** ৳800 - ৳1,500/month

**Description:**
Fee for school meal/tiffin service.

**Meal Plans:**
```
Tiffin Only (Snack):          ৳800/month
Tiffin + Lunch:               ৳1,500/month
Lunch Only:                   ৳1,200/month
```

**Business Rules:**
- Optional service
- Monthly advance payment
- Can opt-in/opt-out each month
- Adjusted for holidays/absences

**Payment Timing:** By 1st of each month

---

### 13. Online Fee (অনলাইন ফি)

**Code:** `ONLINE_FEE`
**Category:** `online_fee`
**Payment Frequency:** Yearly or per service
**Mandatory:** Optional
**Typical Amount:** ৳500 - ৳1,500/year

**Description:**
Fee for online services and digital platforms.

**Services Included:**
```
Online Learning Platform:      ৳1,000/year
SMS Notification Service:      ৳300/year
Parent Portal Access:          ৳500/year
Digital Content Access:        ৳800/year
```

**Business Rules:**
- Increasingly mandatory for modern schools
- One-time or annual subscription
- Covers platform maintenance and content

**Payment Timing:** At enrollment or yearly renewal

---

### 14. Miscellaneous (বিবিধ)

**Code:** `MISCELLANEOUS`
**Category:** `miscellaneous`
**Payment Frequency:** As needed
**Mandatory:** Varies
**Typical Amount:** Variable

**Description:**
Any other fees not covered by above categories.

**Examples:**
```
Library Fine:                  ৳50-500
Lost ID Card Replacement:      ৳150
Damage to School Property:     Variable
Late Admission Fee:            ৳1,000
Extra Classes/Coaching:        ৳1,500/month
Study Tour/Field Trip:         ৳2,000-5,000
```

**Business Rules:**
- Case-by-case basis
- Approved by principal
- Documented with reason
- Separate receipt for each

**Payment Timing:** As incurred

---

## Fee Payment Rules

### Due Dates

| Fee Type | Due Date | Late Fee |
|----------|----------|----------|
| Monthly Tuition | 5th of month | ৳100 after 15th |
| Exam Fees | 2 weeks before exam | Cannot sit exam if unpaid |
| Session Fee | Within first month of year | ৳200 after 1 month |
| Board Fees | As per board schedule | Cannot register if unpaid |

### Payment Priority

When partial payment is made, apply in this order:
1. Overdue fees first
2. Current month tuition
3. Exam fees (if exam is approaching)
4. Other mandatory fees
5. Optional fees

### Waiver/Scholarship Policy

Eligible for fee waiver:
- Economically disadvantaged families
- Merit-based scholarships
- Orphans/single-parent families
- Children of freedom fighters

**Waiver Amounts:**
```
100% waiver: Full tuition + session fee
75% waiver: Tuition fee only
50% waiver: 50% of tuition fee
25% waiver: 25% of tuition fee
```

**Fees NOT waived:**
- Exam fees (board regulations)
- Optional services (tiffin, transport)
- One-time fees (admission form)

---

## Fee Collection Schedule

### At New Admission (After Approval)
```
Required Immediately:
1. Admission Form Fee*          (already paid)
2. Admission Fee                ৳5,000
3. Session Fee                  ৳3,000
4. First Month Tuition          ৳2,000
5. Badge/ID Package             ৳500
6. Development Fee              ৳2,000
-------------------------------------------
Total Package:                  ৳12,500
(*Form fee of ৳200 already paid before approval)
```

### Monthly (Regular Students)
```
Due on 5th of each month:
1. Monthly Tuition              ৳2,000
2. Tiffin Fee (if opted-in)     ৳800
-------------------------------------------
Total:                          ৳2,800
```

### Per Term/Exam
```
Before terminal exams (2-3 times/year):
1. Exam Fee                     ৳800
```

### Yearly (Continuing Students)
```
At start of new academic year:
1. Re-admission Fee             ৳5,000
2. Session Fee                  ৳3,000
3. Development Fee              ৳2,000
4. Online Fee (if applicable)   ৳1,000
-------------------------------------------
Total:                          ৳11,000
```

---

## Database Seeder

### Fee Types Seeder

```php
<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\FeeType;

class FeeTypeSeeder extends Seeder
{
    public function run()
    {
        $feeTypes = [
            [
                'code' => 'ADM_FORM',
                'name' => 'Admission Form Fee',
                'name_bn' => 'ভর্তি ফরম ফি',
                'category' => 'admission_form',
                'default_amount' => 200.00,
                'is_one_time' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'ADM_FEE',
                'name' => 'Admission / Re-Admission Fee',
                'name_bn' => 'ভর্তি / পুনঃভর্তি ফি',
                'category' => 'admission_fee',
                'default_amount' => 5000.00,
                'is_one_time' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'TUITION',
                'name' => 'Monthly Tuition Fee',
                'name_bn' => 'মাসিক টিউশন ফি',
                'category' => 'monthly_tuition',
                'default_amount' => 2000.00,
                'is_one_time' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'SESSION_FEE',
                'name' => 'Session Fee',
                'name_bn' => 'সেশন ফি',
                'category' => 'session_fee',
                'default_amount' => 3000.00,
                'is_one_time' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'EXAM_FEE',
                'name' => 'Exam Fee / Board Fee',
                'name_bn' => 'পরীক্ষা ফি / বোর্ড ফি',
                'category' => 'exam_fee',
                'default_amount' => 800.00,
                'is_one_time' => false,
                'sort_order' => 5,
            ],
            [
                'code' => 'REGISTRATION_FEE',
                'name' => 'Registration Fee',
                'name_bn' => 'নিবন্ধন ফি',
                'category' => 'registration_fee',
                'default_amount' => 1500.00,
                'is_one_time' => true,
                'sort_order' => 6,
            ],
            [
                'code' => 'FORM_FILLUP',
                'name' => 'Form Fill-Up Fee',
                'name_bn' => 'ফরম পূরণ ফি',
                'category' => 'form_fillup',
                'default_amount' => 300.00,
                'is_one_time' => false,
                'sort_order' => 7,
            ],
            [
                'code' => 'BADGE_SASH',
                'name' => 'Badge / Sash',
                'name_bn' => 'ব্যাজ / স্যাশ',
                'category' => 'badge_sash',
                'default_amount' => 500.00,
                'is_one_time' => true,
                'sort_order' => 8,
            ],
            [
                'code' => 'PROSPECTUS_CERT',
                'name' => 'Prospectus / Certificate / T-Shirt / Number Slip',
                'name_bn' => 'প্রসপেক্টাস / সার্টিফিকেট / টি-শার্ট / নম্বর স্লিপ',
                'category' => 'prospectus_cert',
                'default_amount' => 200.00,
                'is_one_time' => false,
                'sort_order' => 9,
            ],
            [
                'code' => 'BSC_SCOUT_SPORTS',
                'name' => 'B.S.C / Scout / Sports',
                'name_bn' => 'বি.এস.সি / স্কাউট / খেলাধুলা',
                'category' => 'bsc_scout_sports',
                'default_amount' => 1000.00,
                'is_one_time' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'BUILDING_DEV',
                'name' => 'Building / Development Fee',
                'name_bn' => 'ভবন / উন্নয়ন ফি',
                'category' => 'building_dev',
                'default_amount' => 2000.00,
                'is_one_time' => true,
                'sort_order' => 11,
            ],
            [
                'code' => 'TIFFIN_MEAL',
                'name' => 'Tiffin / Meal Fee',
                'name_bn' => 'টিফিন / খাবার ফি',
                'category' => 'tiffin_meal',
                'default_amount' => 800.00,
                'is_one_time' => false,
                'sort_order' => 12,
            ],
            [
                'code' => 'ONLINE_FEE',
                'name' => 'Online Fee',
                'name_bn' => 'অনলাইন ফি',
                'category' => 'online_fee',
                'default_amount' => 1000.00,
                'is_one_time' => true,
                'sort_order' => 13,
            ],
            [
                'code' => 'MISCELLANEOUS',
                'name' => 'Miscellaneous',
                'name_bn' => 'বিবিধ',
                'category' => 'miscellaneous',
                'default_amount' => 0.00,
                'is_one_time' => false,
                'sort_order' => 14,
            ],
        ];

        foreach ($feeTypes as $feeType) {
            FeeType::create($feeType);
        }
    }
}
```

---

## Reporting Requirements

### Monthly Fee Collection Report
```
Show for each fee type:
- Number of payments
- Total amount collected
- Class-wise breakdown
- Payment method breakdown
```

### Outstanding Fees Report
```
List students with:
- Unpaid fees by type
- Amount overdue
- Days overdue
- Contact information
```

### Fee Waiver Report
```
Track:
- Number of students with waivers
- Total amount waived
- Waiver category (merit/financial/other)
- Approval status
```

---

**End of Document**
