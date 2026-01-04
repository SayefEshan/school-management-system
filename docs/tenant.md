### **Revised Tenant App User Flow**

The Tenant App is designed to simplify the rental process for tenants, from onboarding to property management, payments, and repairs. Here's a step-by-step breakdown of the tenant experience:

## **1\. Onboarding**

**Start**: The tenant launches the app and is greeted with a user-friendly interface.

### **Steps:**

- **Select Role**:
  - Choose the "Tenant" role to proceed.
  - _Example_: Sarah, a new user, selects "Tenant" to get started.
- **Sign Up**:
  - Provide basic details:
    - Name
    - Email (with email verification)
    - Phone number (with OTP verification)
    - Password
  - _Scenario_: John inputs his details, sets a secure password, and verifies his email to proceed.
- **Provide Additional Information**:
  - Weekly rent budget.
  - Preferred suburbs.
  - Move-in timeline (e.g., immediate, 1 month, 3 months).
  - Additional preferences:
    - Pets
    - Minimum number of bedrooms and bathrooms.
  - _Example_: Sarah sets a budget of \$500/week, selects "Melbourne CBD," and mentions that she has a pet dog.
- **Join Waitlist (if needed)**:
  - If no matching properties are available, tenants are added to a waitlist.
  - Notification:
    - "Thank you for joining the waitlist. We will notify you when properties match your preferences."
  - _Scenario_: Sarah completes her profile and joins the waitlist, awaiting notifications about suitable properties.

**End**: The tenant is onboarded and ready to explore app features or await matching properties.

## **2\. Property Search and Inspection Requests**

**Start**: The tenant logs in and navigates to the property search module.

### **Steps:**

- **Apply Search Filters**:
  - Set preferences such as:
    - Rent budget
    - Suburb
    - Property type (e.g., apartment, house)
    - Amenities (e.g., pet-friendly, parking)
  - _Example_: John searches for 1-bedroom apartments in "Melbourne CBD" under \$450/week.
- **View Listings**:
  - Details include:
    - Images of the property.
    - Augmented Reality (AR) tours (if enabled and purchased by the landlord).
    - Rent amount.
    - Inspection availability.
  - _Scenario_: Sarah views an apartment listing with AR features, allowing her to explore the property virtually.
- **Schedule an Inspection**:
  - Options:
    - In-person (IP) inspection.
    - Augmented Reality (AR) inspection.
  - Actions:
    - Choose a date and time.
    - Register for the inspection by submitting ID (e.g., Driver's ID/Student ID).
  - Notifications:
    - Tenant: "Your inspection request for 15 Collins St has been submitted."
    - Landlord: "Tenant Sarah has requested an inspection for 15 Collins St on Jan 25, 2025."
  - _Scenario_: John schedules an in-person inspection for Jan 25, 2025, at 10 AM and uploads his ID for verification.
- **View Scheduled Inspections**:
  - Inspections are listed in the tenant's calendar for easy tracking.

**End**: The inspection request is submitted, and the tenant awaits confirmation from the landlord.

## **3\. Application Process**

**Start**: The tenant selects a property and begins the application process.

### **Steps:**

- **Complete the Application Form**:
  - Provide details such as:
    - Profile information (e.g., employment, rental history).
    - Credit check authorization (via Experian API).
    - Upload supporting documents (e.g., proof of income, rental history).
    - Accept legal terms (digitally sign).
  - _Scenario_: Sarah uploads her payslip and rental history, and consents to a credit check.
- **Submit Application**:
  - Notifications:
    - Tenant: "Your application has been successfully submitted."
    - Landlord: "Tenant Sarah has applied for 15 Collins St. Please review the application."
- **Track Application Status**:
  - Status updates include:
    - Submitted.
    - Credit Check Approved.
    - Assessed Rental Threshold.
    - Reference Checked.
    - Approved/Rejected.
  - _Rejection Reasons_:
    - A - Stronger Applicant was chosen.
    - B - Property unsuitable for pets.
    - C - Credit-based decision.

**End**: The tenant receives a notification regarding application approval or rejection.

## **4\. Payment Management**

**Start**: The tenant sets up payment preferences and manages rent payments.

### **Steps:**

- **Set Up Payment Methods**:
  - Add a credit card or bank account.
  - Sign a missed payments agreement (e.g., \$15 fee for missed payments, plus \$5/day thereafter).
- **Enable Direct Debit**:
  - Automate rent payments to avoid late fees (\$45 for failed payments).
- **View Payment Details**:
  - Upcoming Payments: "Feb 1, 2025 - \$550."
  - Payment History: Chronological breakdown of past payments.
  - _Scenario_: Sarah automates her rent payments to deduct \$550 from her bank account on the 1st of each month.

**End**: Payments are seamlessly processed, and tenants can view a complete history.

## **5\. Repairs and Maintenance**

**Start**: Tenants report issues and track repairs.

### **Steps:**

- **Submit a Repair Request**:
  - Add issue details (e.g., "Leaking faucet").
  - Attach photos for better context.
  - Categorize as urgent or non-urgent.
  - _Scenario_: Sarah submits a repair request for a leaking faucet with a photo attached.
- **Track Repair Status**:
  - Updates include:
    - Assigned to a service provider (e.g., "Mike's Plumbing").
    - Completion status.
  - Notifications:
    - Tenant: "Repair request has been assigned to Mike's Plumbing."
    - Landlord: "Repair completed by Mike's Plumbing."

**End**: The repair ticket is closed once resolved.

<br/>

**6\. Additional Features**

**Start:** Tenants access various tools to manage preferences, billing, inspections, and additional services.

**Profile Management**

- **View and Update Personal Details**
  - Add or update personal details (e.g., name, email, phone).
  - _Scenario:_ John updates his email address from "<john.doe@gmail.com>" to "<j.doe@yahoo.com>."
- **Update Preferences**
  - Set preferences like rent budget, preferred suburbs, and move-in timeline.
  - _Scenario:_ Sarah selects a rent budget of \$2,500 and adds "Richmond" as her preferred suburb.
- **Access Additional Services Menu**
  - View options such as movers, cleaners, gardeners, and legal advice.

**Settings Management**

- **Manage App Preferences**
  - Customize settings like notification preferences and dark/light mode.
  - _Scenario:_ Alex enables dark mode and turns off push notifications.
- **Enable/Disable Auto-Pay for Rent**
  - Easily turn auto-pay on or off for rent payments.

**Billing Management**

- **Payment Methods**
  - Add, edit, or delete bank account or credit card details.
  - _Scenario:_ David removes his expired credit card and adds a new one for rent payments.
- **View and Download Rent Receipts**
  - Access past rent receipts for records.
- **Set Alerts for Payments**
  - Receive reminders for upcoming rent payments.

**Support Options**

- **Access FAQs**
  - Resolve common issues using a database of frequently asked questions.
- **Raise Tickets**
  - Submit unresolved issues to support with detailed descriptions.
- **Contact Support**
  - Connect with support via chat, email, or phone.

**Inspection Management**

- **Scheduled Inspections**
  - View a list of upcoming property inspections.
- **Inspection History**
  - Access records of past inspections for reference.

**Additional Services Menu**

- **Connect Utilities**
  - Request help setting up utilities like electricity, water, or internet.
- **Movers & Packers**
  - Book moving services for your convenience.
- **Gardeners**
  - Request garden maintenance services.
- **Legal Advice**
  - Obtain guidance for tenancy-related legal matters.
- **VCAT Matter Assistance**
  - Get support for Victorian Civil and Administrative Tribunal (VCAT) issues.
- **VCAT Representation**
  - Request representation for VCAT matters.

#### **Admin-Handled Workflow for Additional Services**

- **Service Creation**
  - Admin sets up and manages service options in the app.
- **Tenant Request Notification**
  - Whenever a tenant requests a service, the admin is notified.
- **Service Tracking**
  - Admin manually updates the service status (e.g., "Pending," "In Progress," or "Completed").
- **Tenant Notifications**
  - Tenants are notified for every status update.
  - _Scenario:_ "Your gardening service request has been marked as 'Completed' by the admin."
- **Example Flow**
  - A tenant requests a legal advice service.
  - The admin assigns it to a legal professional and updates the status to "In Progress."
  - Once resolved, the admin marks it as "Completed," and the tenant receives a notification.

**End:** Tenants can manage all services, preferences, and settings with admin support where necessary.

&nbsp;

### **7\. Tenant Dashboard Information**

**Start:** The Tenant Dashboard serves as a centralized hub where tenants can seamlessly manage their properties, payments, repair requests, and interactions.

#### **Steps**

**Dashboard Overview**

- **Welcome Section**
  - Personalized greeting with key reminders for tenants.
  - _Example:_ "Welcome, Sarah! Your next rent payment is due on Feb 1, 2025."
- **Upcoming Payments**
  - Display next rent payment details:
    - _Example:_ Next Payment: Feb 1, 2025 - \$550.
    - Payment status: Pending/Processing/Completed.
  - Option to enable/disable Auto-Pay.
- **Payment History**
  - Chronologically listed recent payments with transaction details.
  - _Examples:_
    - Jan 1, 2025 - \$550 Paid via Visa \*\*\*\*1234.
    - Dec 1, 2024 - \$550 Paid via Visa \*\*\*\*1234.

**Active Properties**

- **Property Details**
  - Address, size of the land, and build dimensions for property layout.
  - Lease duration and monthly rent.
  - _Example:_ 15 Collins St, Melbourne. Lease duration: Jan 2025 - Jan 2026. Monthly rent: \$550.
- **Landlord or Agent Contact**
  - Contact details for the landlord or agent.
  - _Example:_ John Doe (Phone/Email).
  - Displayed only if the landlord manages the property directly.
- **Property Profile & Rating**
  - View the current property profile and its rating, derived from the landlord's profile.

**Repair Requests**

- **Status Breakdown**
  - Active requests: Details about ongoing repairs.
    - _Example:_ "Leaking faucet - Assigned to Mike's Plumbing."
  - Completed repairs: History of resolved issues.
    - _Example:_ "Broken window - Fixed by Swift Repairs on Jan 20, 2025."

**Notifications**

- **Reminders**
  - Notifications for upcoming rent payments, inspections, and other critical events.
  - _Examples:_
    - "Rent payment for Feb 1, 2025, is upcoming."
    - "Inspection scheduled for Jan 25, 2025, at 10 AM."

**Dashboard Features**

- **Quick Actions**
  - Submit a repair request.
  - Update payment method.
  - Contact landlord.
  - Track dates, payments, and types of additional services engaged.
- **Insights**
  - Display useful tenancy-related metrics.
    - _Examples:_
      - Total rent paid: \$6,600 (2024).
      - Repair requests filed: 4.
      - Issues mitigated during tenancy.
- **Landlord Rating and Review**
  - Option to rate and review the landlord once the tenancy ends.

**End:** The Tenant Dashboard empowers tenants to manage all aspects of their rental experience efficiently, from payments to property interactions.
