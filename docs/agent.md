### **Agent User Flow**

This flow includes agent onboarding, creation by both landlord and admin, certification, signed contract management, property and task management, and reporting.

### **1\. Agent Creation**

#### **Scenario A: Admin Creates an Agent (Direct Creation)**

**Start:** Admin logs into the platform and navigates to the "Agent Management" section.

- **Create Agent:**
  - Admin fills in agent details:
    - Name
    - Email
    - Phone (optional for 2FA)
    - License or REIA Certification (uploaded as a document)
    - Signed Contract (optional, uploaded as a record).
  - Assign properties (optional): Admin can immediately assign properties for the agent to manage.
- **Instant Approval:**
  - The system auto-approves the agent.
- **Email Notification:**
  - The agent receives an email with login credentials:
    - **Subject:** "Welcome to \[Platform Name\] - Your Agent Credentials"

**Email Body:  
**less  
CopyEdit  
Dear \[Agent Name\],

Congratulations! You've been successfully added as an agent.

Login credentials:

\- User ID: \[Generated ID\]

\- Password: \[Generated Password\]

Please log in to the platform and update your password for security.

Best regards,

\[Platform Name\] Team

- **Confirmation Notification to Admin:**
  - "Agent \[Name\] has been successfully created and notified."

**End:** The agent account becomes active immediately.

#### **Scenario B: Landlord Creates an Agent (Approval Required)**

**Start:** Landlord logs into the platform and navigates to the "Agent Management" section.

- **Create Agent Request:**
  - Landlord fills in agent details:
    - Name
    - Email
    - Phone (optional for 2FA)
    - License or REIA Certification (uploaded as a document).
    - Signed Contract (optional, uploaded as a record).
  - Assign properties (optional): Landlord can assign properties for the agent to manage.
- **Submit for Approval:**
  - The system sends the request to the admin for review.
  - Status: Pending Admin Approval.
- **Admin Approval Process:**
  - **Approve:**
    - Admin reviews the request and approves.
    - Email notification is sent to the agent (same as above).
    - Landlord is notified: "Agent \[Name\] has been approved and is now active."
  - **Reject:**
    - Admin rejects the request.
    - Landlord is notified: "Agent creation request for \[Name\] was rejected. Reason: \[Admin's Note\]."

**End:** The agent account becomes active after admin approval.

### **2\. Certification & Contract Management**

- **License or REIA Certification:**
  - Mandatory field during agent creation.
  - Admin/Landlord uploads a valid certification document (e.g., PDF, JPEG).
- **Signed Contract (Optional):**
  - Admin or Landlord may upload a signed contract for record-keeping.
  - The agent can view the uploaded document in their profile under "Documents."
- **Verification:**
  - Admin verifies the uploaded license and contract during the approval process.

**End:** Verified documents are stored securely and linked to the agent's profile.

### **3\. Agent Onboarding**

**Start:** Agent receives an email with login credentials.

- **Login:**
  - Agent logs in using the provided credentials.
  - Prompts to update the password for security.
- **Complete Profile:**
  - Update personal information (optional).
  - Upload a profile picture (optional).
- **View Documents:**
  - Agent views uploaded license and contract under their profile in the "Documents" section.

**End:** Agent account is fully set up and ready for use.

### **4\. Agent Dashboard Features**

#### **Dashboard Overview**

- **Welcome Section:** Personalized greeting.  
    Example: "Welcome, \[Agent Name\]! You have 3 tasks to complete today."
- **Assigned Properties:**
  - Displays a list of properties assigned by the landlord or admin.
  - Example:
    - 15 Collins St - Rent: \$550/week
    - 20 Green St - Rent: \$650/week
- **Notifications:**
  - Alerts about tasks, inspections, or certifications expiring soon.
  - Example: "Your REIA certification expires on March 15, 2025. Please renew."

### **5\. Property Management by Agent**

- **View Assigned Properties:**
  - Agents can view all properties assigned to them.
  - Property details include:
    - Address
    - Rent amount
    - Landlord name
    - Tenant name
- **Add/Edit Property Details (Optional):**
  - Add property images, inspection details, and condition reports.
- **Manage Tenant Applications:**
  - View pending applications.
  - Approve or reject tenants (if permissions are granted by the landlord).

### **6\. Task Management**

- **Repair Requests:**
  - View tenant-submitted repair requests.
  - Assign tasks to tradespeople.
  - Example: "Leaking faucet at 15 Collins St - Assigned to Mike's Plumbing."
- **Scheduled Inspections:**
  - Manage and conduct property inspections.
  - Example: "Inspection scheduled for 20 Green St on Feb 5, 2025."
- **Condition Reports:**
  - Submit condition reports for properties.
  - Upload detailed photos and notes for each room/area.

### **7\. Communication**

- **Tenant Communication:**
  - Agents can communicate with tenants regarding repairs, inspections, or other issues.
- **Landlord Communication:**
  - Agents can provide updates to landlords on property status, tenant applications, or repair progress.

### **8\. Reporting & Analytics**

- **Payment Reports:**
  - View rent collection reports for assigned properties.
  - Example: "Jan 2025 - Total rent collected: \$4,500."
- **Task Completion Reports:**
  - Track completed tasks like repairs and inspections.
- **Tenant Feedback:**
  - View tenant feedback on repairs or overall property management.

### **9\. Profile Management**

- **Update Personal Information:**
  - Name, Email, Phone, Password.
- **View Documents:**
  - View uploaded license, REIA certification, and signed contracts.
  - Downloadable format for convenience.
- **Property Assignment Overview:**
  - View properties currently assigned.
  - Request reassignment (optional).

### **10\. Logout**

Securely log out of the application.

### **Key Features Summary**

- **Agent Creation:**
  - Admins can create agents instantly.
  - Landlords can request agent creation (requires admin approval).
- **Certification & Contracts:**
  - Upload mandatory License/REIA Certification.
  - Optional signed contracts for record-keeping.
- **Email Notifications:**
  - Agents receive credentials via email upon approval or creation.
- **Property Management:**
  - Agents can manage assigned properties, handle tenant applications, and oversee repairs.
- **Task Management:**
  - Includes repair assignments, scheduled inspections, and condition reports.
- **Reporting:**
  - Agents can access payment, task completion, and feedback reports.

Let me know if there are any additional details you'd like to include!
