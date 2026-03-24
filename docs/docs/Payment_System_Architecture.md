# Payment System Architecture
## Multi-Item Payment System for School Management

**Version:** 2.0
**Last Updated:** November 2025
**Author:** School Management System Team

---

## Overview

This document describes the payment system architecture designed for Bangladeshi schools, supporting multi-item payments in a single transaction with flexible fee selection and preset fee packages.

---

## Key Features

### 1. Multi-Item Transactions
- Single payment transaction can include multiple fee types
- One receipt with itemized breakdown
- Automatic total calculation

### 2. Approve-First, Pay-Later Workflow
- Applications approved FIRST by admin
- Approved applicants become students
- Fees paid AFTER student record created
- All payments linked directly to student accounts

### 3. Fee Package System
- Preset fee packages for common scenarios
- Admin-configurable packages
- Quick fee collection with one click
- Option to select individual fees manually

### 4. Flexible Fee Selection
- Admin manually selects which fees to collect
- Can combine any fee types in one transaction
- Support for custom one-off fees
- Can use packages or select individually

### 5. Application Form Fee (Optional)
- Optional: Charge fee just to obtain application form
- Separate from admission fee
- Collected before application submission
- Independent tracking

### 6. Payment Methods
- Cash (primary)
- Bank Transfer (with reference number)
- Mobile Banking (bKash, Nagad, Rocket - future)
- Cheque (future)

---

## Database Schema

### payment_transactions
Main payment record containing transaction-level information.

```sql
CREATE TABLE payment_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(50) UNIQUE NOT NULL, -- PAY-2025-000001
    student_id BIGINT UNSIGNED NOT NULL, -- FK to students (all payments linked to students)
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking', 'cheque') DEFAULT 'cash',
    payment_date DATE NOT NULL,
    reference_number VARCHAR(100) NULL, -- Bank/mobile reference
    collected_by BIGINT UNSIGNED NOT NULL, -- FK to users
    fee_package_id BIGINT UNSIGNED NULL, -- FK to fee_packages (if using package)
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

### payment_line_items
Individual fee items within each transaction.

```sql
CREATE TABLE payment_line_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_transaction_id BIGINT UNSIGNED NOT NULL,
    fee_type_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL, -- "Admission Fee - Class 6"
    quantity INT UNSIGNED DEFAULT 1, -- For multiple months
    unit_amount DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL, -- quantity × unit_amount
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_type_id) REFERENCES fee_types(id),
    INDEX idx_payment_transaction (payment_transaction_id)
) ENGINE=InnoDB;
```

### fee_types
Master list of all possible fee categories.

```sql
CREATE TABLE fee_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, -- ADM_FORM, ADM_FEE, TUITION
    name VARCHAR(255) NOT NULL, -- Admission Fee
    name_bn VARCHAR(255) NOT NULL, -- ভর্তি ফি
    category ENUM(
        'admission_form', 'admission_fee', 'monthly_tuition',
        'session_fee', 'exam_fee', 'registration_fee',
        'form_fillup', 'badge_sash', 'prospectus_cert',
        'bsc_scout_sports', 'building_dev', 'tiffin_meal',
        'online_fee', 'miscellaneous'
    ) NOT NULL,
    default_amount DECIMAL(10, 2) DEFAULT 0.00,
    is_one_time BOOLEAN DEFAULT TRUE, -- False for monthly/recurring
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;
```

### fee_packages
Preset fee packages for common payment scenarios.

```sql
CREATE TABLE fee_packages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, -- NEW_ADMIT_2025, MONTHLY_REG
    name VARCHAR(255) NOT NULL, -- New Student Admission Package 2025
    name_bn VARCHAR(255) NOT NULL, -- নতুন শিক্ষার্থী ভর্তি প্যাকেজ ২০২৫
    description TEXT NULL,
    package_type ENUM('new_admission', 'monthly_regular', 'annual_regular', 'custom') NOT NULL,
    academic_year_id BIGINT UNSIGNED NULL, -- For year-specific packages
    class_id BIGINT UNSIGNED NULL, -- For class-specific packages (NULL = all classes)
    total_amount DECIMAL(10, 2) NOT NULL, -- Auto-calculated from items
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
Fee types included in each package.

```sql
CREATE TABLE fee_package_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fee_package_id BIGINT UNSIGNED NOT NULL,
    fee_type_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED DEFAULT 1, -- For multiple months
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
Tracks fees paid for application form purchase (before application submission).
Only used by schools that charge for the form itself.

```sql
CREATE TABLE application_form_fees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form_number VARCHAR(50) UNIQUE NOT NULL, -- FORM-2025-00001
    applicant_name VARCHAR(255) NULL, -- May not have full details yet
    applicant_phone VARCHAR(20) NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_banking') DEFAULT 'cash',
    payment_date DATE NOT NULL,
    collected_by BIGINT UNSIGNED NOT NULL, -- FK to users
    form_issued BOOLEAN DEFAULT FALSE,
    form_submitted BOOLEAN DEFAULT FALSE,
    admission_application_id BIGINT UNSIGNED NULL, -- Link when form submitted
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (collected_by) REFERENCES users(id),
    FOREIGN KEY (admission_application_id) REFERENCES admission_applications(id) ON DELETE SET NULL,
    INDEX idx_form_number (form_number),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB;
```

---

## Payment Workflows

### Workflow 1: Approve-First, Pay-Later (Standard Flow)

```
┌─────────────────────────────────────────────┐
│ 1. Applicant submits online application    │
│    Status: pending_review                   │
│    (No payment required yet)                │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 2. Admin reviews application                │
│    - Checks documents                        │
│    - Verifies information                    │
│    - Makes admission decision                │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 3. Admin APPROVES application               │
│    Status: approved                          │
│    Notification sent to parent               │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 4. System creates STUDENT record            │
│    - Student ID: 2506000015                  │
│    - Class: 6, Section: A, Roll: 23          │
│    - Status: active                          │
│    - Admission application linked            │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 5. Parent visits school for fee payment    │
│    Staff searches student by ID/name        │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 6. Staff selects payment method:            │
│    OPTION A: Use Fee Package                │
│      → "New Student Package 2025"           │
│      → Auto-loads all admission fees        │
│                                              │
│    OPTION B: Select Individual Fees         │
│      ☑ Admission Fee (৳5,000)               │
│      ☑ Session Fee (৳3,000)                 │
│      ☑ First Month Tuition (৳2,000)         │
│      ☑ Badge/Sash (৳500)                    │
│      ☑ Prospectus (৳300)                    │
│    Total: ৳10,800                           │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 7. System creates:                           │
│    - payment_transactions (1 record)         │
│      student_id = 2506000015                 │
│      total_amount = 10,800                   │
│    - payment_line_items (5 records)          │
│    - Updates student_fees status to 'paid'   │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 8. Generate & print receipt                 │
│    Receipt #: PAY-2025-000045               │
│    Parent receives copy                      │
└─────────────────────────────────────────────┘
```

### Workflow 2: Optional Application Form Fee

```
┌─────────────────────────────────────────────┐
│ 1. Prospective parent visits school         │
│    Wants to purchase application form       │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 2. Staff collects form fee                  │
│    Amount: ৳200                             │
│    Form Number: FORM-2025-00123             │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 3. System creates:                           │
│    - application_form_fees record            │
│    - Prints form number on receipt           │
│    - Issues blank form to parent             │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 4. Parent fills form and submits online    │
│    Enters form number during submission     │
│    System links form fee to application     │
└─────────────────────────────────────────────┘
```

### Workflow 3: Student Regular Payment

```
┌─────────────────────────────────────────────┐
│ 1. Parent visits for monthly fee payment   │
│    Staff searches student: 2506000015       │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 2. View student fee dashboard:              │
│    Paid: Jan Tuition ✓, Admission ✓        │
│    Due: Feb Tuition (৳2,000)                │
│         March Tuition (৳2,000)              │
│         April Tuition (৳2,000)              │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 3. Staff selects payment method:            │
│    OPTION A: Use Monthly Package            │
│      → "Monthly Regular Package"            │
│      → Includes: Tuition + Tiffin           │
│                                              │
│    OPTION B: Select Individual Fees         │
│      ☑ February Tuition (৳2,000)            │
│      ☑ March Tuition (৳2,000)               │
│      ☑ February Tiffin (৳500)               │
│    Total: ৳4,500                            │
└─────────────────┬───────────────────────────┘
                  │
┌─────────────────▼───────────────────────────┐
│ 4. System creates payment & receipt         │
└─────────────────────────────────────────────┘
```

---

## Fee Package Examples

### Package 1: New Student Admission 2025

```php
Fee Package: "New Student Admission Package 2025"
Code: NEW_ADMIT_2025
Type: new_admission
Academic Year: 2025
Class: All (NULL)

Included Fees:
1. Admission Fee           ৳5,000
2. Session Fee 2025        ৳3,000
3. First Month Tuition     ৳2,000
4. Badge/Sash              ৳500
5. Prospectus              ৳300
─────────────────────────────────
Total:                     ৳10,800
```

### Package 2: Monthly Regular Package

```php
Fee Package: "Monthly Regular Package"
Code: MONTHLY_REG
Type: monthly_regular
Academic Year: NULL (reusable)
Class: All (NULL)

Included Fees:
1. Monthly Tuition         ৳2,000
2. Tiffin Fee              ৳500
─────────────────────────────────
Total:                     ৳2,500
```

### Package 3: Annual Fees Package

```php
Fee Package: "Annual Fees 2025"
Code: ANNUAL_2025
Type: annual_regular
Academic Year: 2025
Class: All (NULL)

Included Fees:
1. Session Fee             ৳3,000
2. Exam Fee (Annual)       ৳2,000
3. Sports/Scout            ৳1,000
4. Building Development    ৳1,500
─────────────────────────────────
Total:                     ৳7,500
```

---

## Transaction Numbering

### Format
```
PAY-{YEAR}-{SEQUENCE}

Examples:
PAY-2025-000001
PAY-2025-000123
PAY-2025-012345
```

### Generation Logic
```php
function generateTransactionNumber(): string
{
    $year = date('Y');
    $prefix = "PAY-{$year}-";

    // Get last transaction number for current year
    $lastTransaction = DB::table('payment_transactions')
        ->where('transaction_number', 'LIKE', "{$prefix}%")
        ->orderBy('transaction_number', 'desc')
        ->value('transaction_number');

    if ($lastTransaction) {
        // Extract sequence and increment
        $lastSequence = intval(substr($lastTransaction, -6));
        $newSequence = $lastSequence + 1;
    } else {
        $newSequence = 1;
    }

    return $prefix . str_pad($newSequence, 6, '0', STR_PAD_LEFT);
}
```

---

## Receipt Generation

### Receipt Format

```
═══════════════════════════════════════════════════════════
         [SCHOOL LOGO]    DHAKA MODEL SCHOOL
                      ঢাকা মডেল স্কুল
═══════════════════════════════════════════════════════════
                    PAYMENT RECEIPT
                    পেমেন্ট রসিদ

Receipt No: PAY-2025-000045              Date: 16-Jan-2025
───────────────────────────────────────────────────────────
Received from / গ্রহণকারী: Md. Abdul Rahman
Student ID / শিক্ষার্থী আইডি: 2506000015
Class / শ্রেণী: 6, Section / শাখা: A, Roll / রোল: 23

───────────────────────────────────────────────────────────
PARTICULARS / বিবরণ                          AMOUNT / টাকা
───────────────────────────────────────────────────────────
Package: New Student Admission Package 2025
প্যাকেজ: নতুন শিক্ষার্থী ভর্তি প্যাকেজ ২০২৫

1. Admission Fee / ভর্তি ফি                   ৳5,000.00
2. Session Fee 2025 / সেশন ফি ২০২৫            ৳3,000.00
3. Monthly Tuition - January / মাসিক বেতন      ৳2,000.00
4. Badge/Sash / ব্যাজ/স্যাশ                    ৳500.00
5. Prospectus / প্রসপেক্টাস                    ৳300.00
───────────────────────────────────────────────────────────
                          TOTAL / মোট:        ৳10,800.00
───────────────────────────────────────────────────────────
Amount in words / কথায়: Ten Thousand Eight Hundred Taka Only
                        দশ হাজার আট শত টাকা মাত্র

Payment Method / পেমেন্ট পদ্ধতি: Cash / নগদ
Received by / গ্রহীতা: Abdul Karim (Accountant / হিসাবরক্ষক)

───────────────────────────────────────────────────────────
                 ___________________________
                    Authorized Signature
                    অনুমোদিত স্বাক্ষর

This is a computer-generated receipt.
এটি একটি কম্পিউটার-উত্পন্ন রসিদ।
═══════════════════════════════════════════════════════════
```

### PDF Generation Service

```php
namespace Modules\Finance\Services;

use Modules\Finance\Models\PaymentTransaction;

class ReceiptService
{
    public static function generateReceipt(int $paymentTransactionId): string
    {
        $payment = PaymentTransaction::with([
            'lineItems.feeType',
            'student.currentEnrollment',
            'feePackage',
            'collector'
        ])->findOrFail($paymentTransactionId);

        $pdf = PDFService::create();

        // Set Bengali font
        $pdf->SetFont('kalpurush', '', 12);

        // Add header with school logo
        $pdf->addSchoolHeader();

        // Add receipt content
        $pdf->addReceiptContent($payment);

        // Add footer with signature
        $pdf->addReceiptFooter($payment);

        // Save to storage
        $path = "receipts/{$payment->transaction_number}.pdf";
        $pdf->save(storage_path("app/public/{$path}"));

        // Update payment record
        $payment->update([
            'receipt_generated' => true,
            'receipt_path' => $path
        ]);

        return $path;
    }
}
```

---

## Payment Service Implementation

```php
namespace Modules\Finance\Services;

use DB;
use Exception;
use Modules\Finance\Models\PaymentTransaction;
use Modules\Finance\Models\PaymentLineItem;
use Modules\Finance\Models\FeePackage;
use Modules\Student\Models\StudentFee;

class PaymentService
{
    /**
     * Collect payment with multiple fee items
     */
    public static function collectPayment(array $data): PaymentTransaction
    {
        try {
            DB::beginTransaction();

            // Generate transaction number
            $transactionNumber = self::generateTransactionNumber();

            // If using package, load package items
            if (isset($data['fee_package_id'])) {
                $package = FeePackage::with('items.feeType')->findOrFail($data['fee_package_id']);
                $data['line_items'] = $package->items->map(function ($item) {
                    return [
                        'fee_type_id' => $item->fee_type_id,
                        'description' => $item->feeType->name,
                        'quantity' => $item->quantity,
                        'unit_amount' => $item->amount,
                        'total_amount' => $item->quantity * $item->amount,
                    ];
                })->toArray();
            }

            // Calculate total
            $total = collect($data['line_items'])->sum('total_amount');

            // Create payment transaction
            $payment = PaymentTransaction::create([
                'transaction_number' => $transactionNumber,
                'student_id' => $data['student_id'],
                'total_amount' => $total,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'collected_by' => $data['collected_by'],
                'fee_package_id' => $data['fee_package_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create line items and update student fees
            foreach ($data['line_items'] as $item) {
                // Create payment line item
                PaymentLineItem::create([
                    'payment_transaction_id' => $payment->id,
                    'fee_type_id' => $item['fee_type_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_amount' => $item['unit_amount'],
                    'total_amount' => $item['total_amount'],
                ]);

                // Update or create student_fees record
                $studentFee = StudentFee::firstOrCreate(
                    [
                        'student_id' => $data['student_id'],
                        'fee_type_id' => $item['fee_type_id'],
                        'academic_year_id' => getCurrentAcademicYear()->id,
                    ],
                    [
                        'amount' => $item['total_amount'],
                        'paid_amount' => 0,
                        'due_amount' => $item['total_amount'],
                        'status' => 'pending',
                    ]
                );

                // Update payment status
                $studentFee->paid_amount += $item['total_amount'];
                $studentFee->due_amount = $studentFee->amount - $studentFee->paid_amount;
                $studentFee->status = $studentFee->due_amount <= 0 ? 'paid' : 'partially_paid';
                $studentFee->save();
            }

            // Generate receipt
            $receiptPath = ReceiptService::generateReceipt($payment->id);

            DB::commit();

            return $payment->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate unique transaction number
     */
    private static function generateTransactionNumber(): string
    {
        $year = date('Y');
        $prefix = "PAY-{$year}-";

        $lastTransaction = PaymentTransaction::where('transaction_number', 'LIKE', "{$prefix}%")
            ->orderBy('transaction_number', 'desc')
            ->value('transaction_number');

        if ($lastTransaction) {
            $lastSequence = intval(substr($lastTransaction, -6));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad($newSequence, 6, '0', STR_PAD_LEFT);
    }
}
```

---

## Fee Package Management Service

```php
namespace Modules\Finance\Services;

use Modules\Finance\Models\FeePackage;
use Modules\Finance\Models\FeePackageItem;
use DB;

class FeePackageService
{
    /**
     * Create a new fee package
     */
    public static function createPackage(array $data): FeePackage
    {
        DB::beginTransaction();

        try {
            // Calculate total from items
            $total = collect($data['items'])->sum(function ($item) {
                return $item['quantity'] * $item['amount'];
            });

            // Create package
            $package = FeePackage::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'name_bn' => $data['name_bn'],
                'description' => $data['description'] ?? null,
                'package_type' => $data['package_type'],
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'class_id' => $data['class_id'] ?? null,
                'total_amount' => $total,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            // Create package items
            foreach ($data['items'] as $index => $item) {
                FeePackageItem::create([
                    'fee_package_id' => $package->id,
                    'fee_type_id' => $item['fee_type_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'amount' => $item['amount'],
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            return $package->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get active packages for a specific context
     */
    public static function getPackagesForContext(
        string $packageType,
        ?int $academicYearId = null,
        ?int $classId = null
    ): \Illuminate\Support\Collection {
        return FeePackage::where('is_active', true)
            ->where('package_type', $packageType)
            ->where(function ($query) use ($academicYearId) {
                $query->whereNull('academic_year_id')
                    ->orWhere('academic_year_id', $academicYearId);
            })
            ->where(function ($query) use ($classId) {
                $query->whereNull('class_id')
                    ->orWhere('class_id', $classId);
            })
            ->orderBy('sort_order')
            ->get();
    }
}
```

---

## Security Considerations

### 1. Transaction Integrity
- All payment operations wrapped in database transactions
- Rollback on any failure
- Audit logging for all payments

### 2. Receipt Tampering Prevention
- Receipts stored with hash verification
- Cannot modify after generation
- Watermark for draft vs final

### 3. Permission Control
```php
Permissions required:
- 'collect payment' - Create payment transactions
- 'view payments' - View payment history
- 'void payment' - Cancel/void transactions
- 'reprint receipt' - Regenerate receipts
- 'manage fee packages' - Create/edit fee packages
```

### 4. Data Validation
- Validate amounts > 0
- Validate payment date not in future
- Validate selected fees exist and are active
- Validate student exists
- Validate package items match package total

---

## Testing Scenarios

### Test 1: New Student Admission with Package

```
Given: Approved application, student created (ID: 2506000015)
When: Collect payment using "New Student Package 2025"
Then:
  - 1 payment_transaction created
  - 5 payment_line_items created (from package)
  - 5 student_fees updated to status = paid
  - fee_package_id set on transaction
  - Receipt generated with package name
```

### Test 2: Individual Fee Selection

```
Given: Student with pending fees
When: Staff manually selects 3 individual fees
Then:
  - 1 payment_transaction created
  - 3 payment_line_items created
  - fee_package_id = NULL
  - 3 student_fees updated
  - Receipt shows individual fees
```

### Test 3: Application Form Fee (Optional)

```
Given: Prospective parent wants application form
When: Collect form fee (৳200)
Then:
  - 1 application_form_fees record created
  - Form number generated (FORM-2025-00123)
  - Receipt printed with form number
  - Form issued to parent
```

### Test 4: Create Fee Package

```
Given: Admin wants to create "New Student Package"
When: Create package with 5 fee items
Then:
  - 1 fee_package created
  - 5 fee_package_items created
  - total_amount = sum of all items
  - Package appears in payment interface
```

---

## Future Enhancements

### Phase 2: Mobile Banking Integration
- bKash API integration
- Nagad API integration
- Rocket payment gateway
- Auto-verification of mobile payments

### Phase 3: Online Payment Portal
- Parent login to view fees
- Online payment via gateway
- Auto-receipt generation
- SMS notification on payment

### Phase 4: Advanced Features
- Partial payments (installments)
- Fee waivers/scholarships
- Bulk payment import
- Automated late fee calculation
- Auto-apply packages based on student context

---

**End of Document**
