# 09 — Administrator Guide

## 9.1 Overview

The administrator module provides tools for managing application configuration, users, access control, projects, budgets, areas, audit trails, and reporting.

The application uses role-based access control (RBAC) combined with scope-based permissions to restrict access to relevant resources.

---

## 9.2 Administrator Responsibilities

Administrators can manage:

- User accounts
- User roles and access scopes
- Projects
- Areas
- Budget configuration
- System settings
- Audit trail
- Activity logs
- Financial reports
- Application notifications

Administrative functions should only be assigned to trusted users.

---

## 9.3 User Management

The user management module allows administrators to:

- Create users
- Edit user information
- Activate or deactivate users
- Assign roles
- Configure access scope
- Review user access

User access should follow the principle of least privilege.

Users should only receive the roles and scopes required for their responsibilities.

---

## 9.4 Role-Based Access Control

The application uses role-based access control.

The main roles include:

| Role | Scope | Responsibility |
|------|-------|----------------|
| ADMIN | Global | System administration |
| FINANCE_MANAGER | Global | Financial review and approval |
| CASHIER | Global | Payment and disbursement processing |
| MAKER | Area | Create financial requests |
| AREA_MANAGER | Area | Review and approve area requests |
| FINANCE_REVIEWER | Project | Financial review |
| PROJECT_MANAGER | Project | Project-level review and approval |
| FINANCE_COORDINATOR | Project | Initial financial approval |

The exact permissions are enforced by application middleware, authorization logic, and access-scope rules.

---

## 9.5 Access Scope

Access can be restricted according to:

- Global access
- Area access
- Project access

This allows the same user to have different roles with different scopes.

For example, a user may have:

```text
FINANCE_REVIEWER
    ↓
Project Scope
    ↓
DEMO-01

## 9.6 Project Management

Administrators can manage projects used by the financial workflow.

The demo environment contains generic projects such as:

Project Code	Project Name
DEMO-01	Community Development Program
DEMO-02	Digital Transformation Program

Project information can be used by the application to:

Categorize financial requests
Associate budgets
Apply project-level access control
Generate project-based reports
Filter financial transactions

Production systems may contain additional projects depending on organizational requirements.

## 9.7 Area Management

Areas can be configured to support scope-based access control.

Administrators can:

Create areas
Edit area information
Configure project-area relationships
Review area-based access

Area configuration should be kept consistent with the organization's access model.

## 9.8 Budget Management

The budget module manages the financial allocation used by the application workflow.

Budget records may contain:

Project
Budget code
Budget name
Allocation
Actual usage
Fiscal year

Example demo data:

Project	Budget	Allocation
DEMO-01	Program Activities	75,000,000
DEMO-01	Community Training	25,000,000
DEMO-01	Community Workshop	20,000,000
DEMO-01	Monitoring and Evaluation	15,000,000
DEMO-02	Digital Transformation	100,000,000

The values above are demonstration data only.

The application validates available budget before processing applicable financial transactions.

## 9.9 Budget Validation

Budget validation is part of the application's business rules.

Before a financial transaction is approved or processed, the application can check whether sufficient budget remains available.

The implementation uses database transaction handling and locking mechanisms for relevant budget operations.

This helps reduce the risk of inconsistent budget balances when multiple transactions are processed concurrently.

## 9.10 System Settings

Administrators can manage configurable application settings from the system settings module.

Examples include:

Application name
General application configuration
Notification settings
Other runtime configuration values

The demo application uses:

Finance Management Demo

Production deployments should use their own appropriate application name and configuration.

## 9.11 Audit Trail

The audit trail records important application activities.

Depending on the event, audit information may include:

User
Action
Module
Record affected
Timestamp
Previous value
New value

Audit records are useful for:

Troubleshooting
Accountability
Reviewing changes
Investigating unexpected activity
Supporting internal control processes

Audit data should not be manually modified during normal application operation.

## 9.12 Activity Logs

Activity logs provide an additional view of user activity within the application.

Administrators can use activity information to understand:

Who performed an action
Which module was accessed
When an action occurred
What type of activity was performed

Activity logging is particularly useful during troubleshooting and system monitoring.

## 9.13 Financial Workflow

The application supports a financial workflow that can include:

Budget
   ↓
Financial Request
   ↓
Approval
   ↓
Disbursement
   ↓
Financial Realization
   ↓
Accountability / LPJ
   ↓
Reimbursement
   ↓
Reporting

Different workflow types may use different approval sequences depending on configuration and business rules.

## 9.14 Approval Workflow

Approval processing is controlled by application state and authorization rules.

Typical workflow states may include:

Draft
  ↓
Submitted
  ↓
Under Review
  ↓
Approved / Rejected
  ↓
Disbursed
  ↓
Accountability Submitted
  ↓
Completed

The exact transition rules are implemented in the application logic.

Users should not bypass workflow transitions by directly modifying database records.

## 9.15 Notifications

The application provides in-app notifications for selected workflow events.

Notifications can help users identify:

Pending approvals
Workflow updates
Revisions
Rejections
Other relevant application events

Users should regularly review notifications related to tasks assigned to them.

## 9.16 Reports

Administrators and authorized users can access financial reports according to their permissions.

Reports may provide information such as:

Budget allocation
Budget utilization
Project performance
Area performance
Financial request status
Workflow progress

Report visibility depends on the user's assigned role and scope.

## 9.17 File Attachments

The application supports document attachments for applicable financial transactions.

Supported file types depend on the validation rules implemented by the application.

Administrators should ensure that:

Uploaded files are relevant to the transaction.
Sensitive documents are handled appropriately.
File permissions are configured correctly.
Production documents are not committed to source control.
## 9.18 Security Practices

Administrators should follow these practices:

Use strong passwords.
Do not share administrator credentials.
Assign only the required roles.
Review user access periodically.
Disable unused accounts.
Do not expose .env.
Do not store production credentials in source code.
Do not commit real financial data to Git.
Use HTTPS in production.
Keep application dependencies updated.
Review audit logs when investigating unexpected changes.
Maintain secure database backups outside the source repository.

##9.19 Backup and Recovery

Database backup and recovery should be handled according to the deployment environment's operational procedures.

For a local development environment, database backups can be created using standard MySQL tools.

Example:

mysqldump -u <username> -p <database_name> > backup.sql

Restore example:

mysql -u <username> -p <database_name> < backup.sql

Replace the placeholders with credentials appropriate for your local or authorized environment. Never place real credentials in this documentation.

Production backup procedures should be documented separately and must not contain publicly exposed credentials, private infrastructure details, or sensitive organizational information.

## 9.20 Troubleshooting
Cannot log in

Check:

User account status
Assigned roles
Authentication configuration
Application logs
Database connection

Clear application caches if configuration changes were recently made:

php artisan optimize:clear
User cannot access a resource

Check:

User role.
User scope.
Project or area assignment.
Authorization middleware.
Resource ownership or scope rules.
Budget validation fails

Check:

Project assignment
Budget code
Fiscal year
Available allocation
Existing utilization
Transaction status

Do not manually modify budget balances to bypass application validation.

Notifications are not displayed

Check:

Notification records
User account
Application configuration
Relevant workflow event
Application logs
9.21 Development Environment Reset

For a dedicated local/demo database, the application can be reset using:

php artisan migrate:fresh --seed

This command permanently deletes existing tables and data in the configured database. Never run it against a production database.

##9.22 Administrator Checklist

Before considering an environment ready for use, verify:

 Database connection works
 Migrations completed successfully
 Demo or authorized user accounts are available
 Roles are correctly assigned
 Access scopes are correct
 Projects are configured
 Areas are configured
 Budgets are configured
 File storage works
 Notifications work
 Audit trail records activities
 Reports can be generated
 Application debug mode is disabled in production
 Production credentials are not stored in source control
 Backups are configured according to operational requirements

## 9.23 Portfolio Notes

This administrator guide has been sanitized for public portfolio use.

The repository uses generic project names and demonstration data. Real organizational names, production project codes, hosting information, credentials, database dumps, and private infrastructure details are intentionally excluded.

The underlying application demonstrates concepts including:

Role-based access control
Scope-based authorization
Financial workflow management
Budget validation
Audit logging
Notifications
Document management
Reporting
Laravel application architecture