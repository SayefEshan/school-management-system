# Financial Reports Specification
## Comprehensive Reporting for School Finance Management

**Version:** 1.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

This document specifies all financial reports required for the school management system, including layouts, data requirements, and export formats.

---

## Report Categories

1. **Daily Reports** - Day-to-day collection tracking
2. **Monthly Reports** - Monthly financial summaries
3. **Student Reports** - Fee status per student
4. **Analytical Reports** - Trends and insights
5. **Compliance Reports** - For auditors and authorities

---

## 1. DAILY COLLECTION REPORT

### Purpose
Track all fee collections for a specific date.

### Data Requirements
```sql
SELECT
    pt.transaction_number,
    pt.payment_date,
    pt.payment_time,
    COALESCE(s.name_en, aa.student_name_en) as student_name,
    COALESCE(e.roll_no, '-') as roll_no,
    COALESCE(c.name, '-') as class_name,
    COALESCE(sec.name, '-') as section_name,
    ft.name as fee_type,
    pli.total_amount,
    pt.payment_method,
    u.name as collected_by
FROM payment_transactions pt
LEFT JOIN students s ON pt.student_id = s.id
LEFT JOIN admission_applications aa ON pt.applicant_id = aa.id
LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = 'active'
LEFT JOIN classes c ON e.class_id = c.id
LEFT JOIN sections sec ON e.section_id = sec.id
JOIN payment_line_items pli ON pt.id = pli.payment_transaction_id
JOIN fee_types ft ON pli.fee_type_id = ft.id
JOIN users u ON pt.collected_by = u.id
WHERE DATE(pt.payment_date) = '2025-01-16'
ORDER BY pt.created_at
```

### Report Layout

```
═══════════════════════════════════════════════════════════════════
                    [SCHOOL LOGO & NAME]
                 DAILY COLLECTION REPORT
                    Date: 16 January 2025
═══════════════════════════════════════════════════════════════════

Receipt # | Time  | Student Name      | Class | Fee Type      | Amount
----------|-------|-------------------|-------|---------------|--------
PAY-001   | 09:30 | Md. Rahim        | 6-A   | Tuition (Jan) | ৳2,000
PAY-002   | 10:15 | Fatema Begum     | 7-B   | Exam Fee      | ৳800
PAY-003   | 11:00 | Applicant        | -     | Admission     | ৳5,000
PAY-004   | 14:30 | Sakib Hasan      | 6-A   | Tuition (Jan) | ৳2,000
                                      + Session Fee    ৳3,000
                                                Total: ৳5,000
...

───────────────────────────────────────────────────────────────────
SUMMARY BY PAYMENT METHOD:
Cash:                                                      ৳45,000
Bank Transfer:                                             ৳10,000
Mobile Banking:                                            ৳5,000
                                                   TOTAL: ৳60,000

SUMMARY BY FEE TYPE:
Monthly Tuition:                                           ৳30,000
Admission Fees:                                            ৳15,000
Exam Fees:                                                 ৳8,000
Other Fees:                                                ৳7,000
                                                   TOTAL: ৳60,000

COLLECTED BY: Abdul Karim (Accountant)
TOTAL TRANSACTIONS: 25
TOTAL AMOUNT: ৳60,000

                                            _____________________
                                            Authorized Signature
═══════════════════════════════════════════════════════════════════
```

### Filters
- Date (single day or range)
- Payment method
- Collected by (staff member)
- Fee type

### Export Formats
- PDF (for printing/filing)
- Excel (for analysis)

---

## 2. MONTHLY FEE COLLECTION REPORT

### Purpose
Complete monthly financial summary of all fee collections.

### Data Requirements
```sql
-- Monthly summary
SELECT
    ft.name as fee_type,
    COUNT(DISTINCT pli.id) as payment_count,
    SUM(pli.total_amount) as total_collected,
    AVG(pli.total_amount) as average_amount
FROM payment_transactions pt
JOIN payment_line_items pli ON pt.id = pli.payment_transaction_id
JOIN fee_types ft ON pli.fee_type_id = ft.id
WHERE YEAR(pt.payment_date) = 2025
  AND MONTH(pt.payment_date) = 1
GROUP BY ft.id, ft.name
ORDER BY total_collected DESC
```

### Report Layout

```
═══════════════════════════════════════════════════════════════════
                    [SCHOOL LOGO & NAME]
              MONTHLY FEE COLLECTION REPORT
                    January 2025
═══════════════════════════════════════════════════════════════════

FEE TYPE                    | PAYMENTS | TOTAL AMOUNT | AVG AMOUNT
----------------------------|----------|--------------|------------
Monthly Tuition             |    450   |   ৳900,000   |   ৳2,000
Admission/Re-admission Fee  |     50   |   ৳250,000   |   ৳5,000
Session Fee                 |    100   |   ৳300,000   |   ৳3,000
Exam Fee                    |    200   |   ৳160,000   |   ৳800
Development Fee             |    100   |   ৳200,000   |   ৳2,000
Badge/Sash                  |     50   |    ৳25,000   |   ৳500
Miscellaneous               |     30   |    ৳15,000   |   ৳500
----------------------------|----------|--------------|------------
TOTAL                       |    980   | ৳1,850,000   |   ৳1,888

───────────────────────────────────────────────────────────────────
COLLECTION BY CLASS:
Class 6:                                                  ৳400,000
Class 7:                                                  ৳450,000
Class 8:                                                  ৳500,000
Class 9:                                                  ৳550,000
Class 10:                                                 ৳600,000

COLLECTION BY PAYMENT METHOD:
Cash:                                                   ৳1,600,000
Bank Transfer:                                            ৳200,000
Mobile Banking:                                            ৳50,000

COLLECTION RATE:
Total Students: 500
Students who paid: 425
Collection Rate: 85%

───────────────────────────────────────────────────────────────────
Report Generated: 01-Feb-2025
Generated By: Financial Manager
═══════════════════════════════════════════════════════════════════
```

### Filters
- Month & Year
- Fee type
- Class
- Payment method

---

## 3. FEE DEFAULTER REPORT

### Purpose
Identify students with outstanding/overdue fees.

### Data Requirements
```sql
SELECT
    s.student_id,
    s.name_en,
    s.name_bn,
    e.roll_no,
    c.name as class_name,
    sec.name as section_name,
    s.phone as student_phone,
    g.name_en as guardian_name,
    g.phone as guardian_phone,
    ft.name as fee_type,
    sf.month,
    sf.amount,
    sf.paid_amount,
    sf.due_amount,
    sf.due_date,
    DATEDIFF(CURDATE(), sf.due_date) as days_overdue,
    sf.status
FROM student_fees sf
JOIN students s ON sf.student_id = s.id
JOIN enrollments e ON s.id = e.student_id AND e.status = 'active'
JOIN classes c ON e.class_id = c.id
LEFT JOIN sections sec ON e.section_id = sec.id
JOIN fee_types ft ON sf.fee_type_id = ft.id
LEFT JOIN student_guardian sg ON s.id = sg.student_id AND sg.is_primary = 1
LEFT JOIN guardians g ON sg.guardian_id = g.id
WHERE sf.status IN ('pending', 'partially_paid', 'overdue')
  AND sf.due_amount > 0
ORDER BY c.order, e.roll_no, sf.due_date
```

### Report Layout

```
═══════════════════════════════════════════════════════════════════
                    [SCHOOL LOGO & NAME]
                  FEE DEFAULTER REPORT
                    As of: 16 January 2025
═══════════════════════════════════════════════════════════════════

CLASS 6, SECTION A
───────────────────────────────────────────────────────────────────
Roll | Student Name         | Fee Type  | Amount | Days | Contact
-----|----------------------|-----------|--------|------|----------
  5  | Md. Rahim            | Tuition   | ৳2,000 |  15  | 01712...
     | (ID: 2506000005)     | Exam Fee  | ৳800   |   8  |
     | Guardian: Father     |           | Total: ৳2,800
     |                      |
 12  | Fatema Begum         | Tuition   | ৳2,000 |  30  | 01812...
     | (ID: 2506000012)     |           |
     | Guardian: Mother     |

CLASS 6, SECTION B
───────────────────────────────────────────────────────────────────
...

───────────────────────────────────────────────────────────────────
SUMMARY:
Total Defaulters: 45 students
Total Outstanding: ৳150,000

By Fee Type:
- Monthly Tuition: ৳100,000 (35 students)
- Exam Fees: ৳30,000 (20 students)
- Other Fees: ৳20,000 (15 students)

By Overdue Period:
- 1-15 days: 20 students (৳60,000)
- 16-30 days: 15 students (৳50,000)
- 30+ days: 10 students (৳40,000)

───────────────────────────────────────────────────────────────────
ACTION REQUIRED:
1. Send reminder SMS to all guardians
2. Schedule parent meetings for 30+ days overdue
3. Consider late fee charges (৳100 per month)
═══════════════════════════════════════════════════════════════════
```

### Filters
- Class & Section
- Overdue days (>15, >30, >60)
- Amount range
- Fee type

---

## 4. STUDENT FEE STATEMENT

### Purpose
Individual student's complete fee history.

### Data Requirements
```sql
-- Student fees
SELECT
    ft.name as fee_type,
    sf.month,
    sf.amount,
    sf.paid_amount,
    sf.due_amount,
    sf.due_date,
    sf.status
FROM student_fees sf
JOIN fee_types ft ON sf.fee_type_id = ft.id
WHERE sf.student_id = 123
ORDER BY sf.due_date

-- Payment history
SELECT
    pt.transaction_number,
    pt.payment_date,
    pli.description,
    pli.total_amount,
    pt.payment_method
FROM payment_transactions pt
JOIN payment_line_items pli ON pt.id = pli.payment_transaction_id
WHERE pt.student_id = 123
ORDER BY pt.payment_date DESC
```

### Report Layout

```
═══════════════════════════════════════════════════════════════════
                    [SCHOOL LOGO & NAME]
                  STUDENT FEE STATEMENT
═══════════════════════════════════════════════════════════════════

Student ID: 2506000015
Name: Md. Abdul Rahman (মোঃ আব্দুল রহমান)
Class: 6, Section: A, Roll: 15
Guardian: Abdul Karim (Father) - 01712345678

Academic Year: 2025
Statement Date: 16 January 2025

───────────────────────────────────────────────────────────────────
FEE SUMMARY:
Total Fees Assigned:                                    ৳35,000
Total Paid:                                             ৳20,000
Outstanding:                                            ৳15,000

───────────────────────────────────────────────────────────────────
ASSIGNED FEES:

Fee Type          | Month    | Amount  | Paid   | Due    | Status
------------------|----------|---------|--------|--------|----------
Admission Fee     | -        | ৳5,000  | ৳5,000 | ৳0     | Paid
Session Fee       | -        | ৳3,000  | ৳3,000 | ৳0     | Paid
Monthly Tuition   | January  | ৳2,000  | ৳2,000 | ৳0     | Paid
Monthly Tuition   | February | ৳2,000  | ৳0     | ৳2,000 | Pending
Monthly Tuition   | March    | ৳2,000  | ৳0     | ৳2,000 | Pending
Exam Fee          | April    | ৳800    | ৳0     | ৳800   | Pending
...

───────────────────────────────────────────────────────────────────
PAYMENT HISTORY:

Date       | Receipt#    | Description            | Amount  | Method
-----------|-------------|------------------------|---------|-------
15-Jan-25  | PAY-000045  | Admission Package:     |         | Cash
           |             | - Admission Fee        | ৳5,000  |
           |             | - Session Fee          | ৳3,000  |
           |             | - January Tuition      | ৳2,000  |
           |             | TOTAL:                 | ৳10,000 |
...

───────────────────────────────────────────────────────────────────
NEXT PAYMENT DUE:
February Tuition - ৳2,000 - Due: 05-Feb-2025

                                            _____________________
                                            Authorized Signature
═══════════════════════════════════════════════════════════════════
```

---

## 5. INCOME & EXPENSE STATEMENT

### Purpose
Complete financial statement showing all income and expenses.

### Data Requirements
```sql
-- Fee income
SELECT 'Fee Collection' as source, ft.name, SUM(pli.total_amount) as amount
FROM payment_transactions pt
JOIN payment_line_items pli ON pt.id = pli.payment_transaction_id
JOIN fee_types ft ON pli.fee_type_id = ft.id
WHERE YEAR(pt.payment_date) = 2025 AND MONTH(pt.payment_date) = 1
GROUP BY ft.id

-- Other income
SELECT category as source, description as name, SUM(amount) as amount
FROM incomes
WHERE YEAR(income_date) = 2025 AND MONTH(income_date) = 1
GROUP BY category

-- Expenses
SELECT ec.name as category, SUM(e.amount) as amount
FROM expenses e
JOIN expense_categories ec ON e.category_id = ec.id
WHERE YEAR(e.expense_date) = 2025 AND MONTH(e.expense_date) = 1
GROUP BY ec.id
```

### Report Layout

```
═══════════════════════════════════════════════════════════════════
                    [SCHOOL LOGO & NAME]
              INCOME & EXPENSE STATEMENT
                    January 2025
═══════════════════════════════════════════════════════════════════

INCOME:
═══════════════════════════════════════════════════════════════════

A. Fee Collections:
   - Admission/Re-admission Fees          ৳250,000
   - Monthly Tuition                      ৳900,000
   - Session Fees                         ৳300,000
   - Exam Fees                            ৳160,000
   - Development Fees                     ৳200,000
   - Other Fees                           ৳40,000
   ───────────────────────────────────────────────
   Subtotal Fee Income:                 ৳1,850,000

B. Other Income:
   - Donations                            ৳100,000
   - Event Sponsorships                    ৳50,000
   - Facility Rental                       ৳20,000
   - Interest Income                       ৳5,000
   ───────────────────────────────────────────────
   Subtotal Other Income:                 ৳175,000

TOTAL INCOME:                           ৳2,025,000

───────────────────────────────────────────────────────────────────

EXPENSES:
═══════════════════════════════════════════════════════════════════

A. Personnel Costs:
   - Teacher Salaries                     ৳800,000
   - Staff Salaries                       ৳200,000
   - Benefits & Allowances                ৳100,000
   ───────────────────────────────────────────────
   Subtotal Personnel:                  ৳1,100,000

B. Operating Expenses:
   - Utilities (Electricity, Water)        ৳80,000
   - Internet & Phone                      ৳20,000
   - Cleaning & Maintenance                ৳40,000
   - Office Supplies                       ৳30,000
   - Transportation                        ৳25,000
   ───────────────────────────────────────────────
   Subtotal Operating:                    ৳195,000

C. Educational Expenses:
   - Books & Learning Materials            ৳100,000
   - Laboratory Equipment                   ৳50,000
   - Sports Equipment                       ৳30,000
   - Library Books                          ৳20,000
   ───────────────────────────────────────────────
   Subtotal Educational:                  ৳200,000

D. Other Expenses:
   - Insurance                              ৳15,000
   - Legal & Professional Fees              ৳10,000
   - Miscellaneous                          ৳5,000
   ───────────────────────────────────────────────
   Subtotal Other:                         ৳30,000

TOTAL EXPENSES:                         ৳1,525,000

═══════════════════════════════════════════════════════════════════
NET INCOME (SURPLUS):                     ৳500,000
═══════════════════════════════════════════════════════════════════

Opening Balance (01-Jan-2025):            ৳500,000
Net Income for January:                   ৳500,000
Closing Balance (31-Jan-2025):          ৳1,000,000

═══════════════════════════════════════════════════════════════════
Report Generated: 01-Feb-2025
Prepared By: Financial Manager
Approved By: Principal
═══════════════════════════════════════════════════════════════════
```

---

## 6. CLASS-WISE FEE COLLECTION

### Purpose
Compare fee collection performance across classes.

### Report Layout

```
═══════════════════════════════════════════════════════════════════
                    [SCHOOL LOGO & NAME]
            CLASS-WISE FEE COLLECTION REPORT
                    January 2025
═══════════════════════════════════════════════════════════════════

Class | Total   | Expected | Collected | Collection | Outstanding
      | Students| Amount   | Amount    | Rate       | Amount
------|---------|----------|-----------|------------|-------------
  6   |   100   | ৳400,000 | ৳350,000  |    88%     |  ৳50,000
  7   |   120   | ৳480,000 | ৳420,000  |    88%     |  ৳60,000
  8   |   110   | ৳500,000 | ৳450,000  |    90%     |  ৳50,000
  9   |    90   | ৳540,000 | ৳500,000  |    93%     |  ৳40,000
 10   |    80   | ৳600,000 | ৳580,000  |    97%     |  ৳20,000
------|---------|----------|-----------|------------|-------------
Total |   500   |৳2,520,000|৳2,300,000 |    91%     | ৳220,000

═══════════════════════════════════════════════════════════════════
```

---

## Dashboard Widgets

### 1. Today's Collection
```
┌─────────────────────────┐
│ TODAY'S COLLECTION      │
├─────────────────────────┤
│ Cash: ৳45,000          │
│ Bank: ৳10,000          │
│ Mobile: ৳5,000         │
├─────────────────────────┤
│ TOTAL: ৳60,000         │
│ Transactions: 25        │
└─────────────────────────┘
```

### 2. This Month Summary
```
┌─────────────────────────┐
│ JANUARY 2025            │
├─────────────────────────┤
│ Income: ৳2,025,000     │
│ Expenses: ৳1,525,000   │
├─────────────────────────┤
│ NET: ৳500,000          │
│ (↑ 20% from last month)│
└─────────────────────────┘
```

### 3. Outstanding Fees
```
┌─────────────────────────┐
│ OUTSTANDING FEES        │
├─────────────────────────┤
│ Total: ৳220,000        │
│ Overdue: ৳80,000       │
├─────────────────────────┤
│ Defaulters: 45 students │
│ [View Report →]         │
└─────────────────────────┘
```

---

## Export Functionality

### PDF Export
- A4 size with school letterhead
- Bilingual (English/Bengali)
- Watermark for draft versions
- Digital signature support
- Print-optimized layout

### Excel Export
- Formatted spreadsheets with formulas
- Multiple sheets for detailed breakdowns
- Pivot-ready data
- Charts and graphs
- Bengali Unicode support

### Implementation
```php
class FinancialReportService
{
    public function exportToPDF(string $reportType, array $filters)
    {
        $pdf = PDFService::create();
        $data = $this->getReportData($reportType, $filters);

        $pdf->loadView("finance::reports.{$reportType}", $data);
        return $pdf->download("{$reportType}_{date('Ymd')}.pdf");
    }

    public function exportToExcel(string $reportType, array $filters)
    {
        $data = $this->getReportData($reportType, $filters);

        return FastExcel::data($data)
            ->export("{$reportType}_{date('Ymd')}.xlsx");
    }
}
```

---

**End of Document**
