# Finance Management System

A portfolio project for a web-based financial management system built with Laravel.

This application demonstrates how a structured financial workflow can be digitized, including fund requests, approval processes, budget validation, disbursement, accountability, and reporting.

## Overview

The system is designed to simulate an organization-level financial management workflow with role-based access control and project/area scoping.

### Main Workflow

Budget
→ Fund Request
→ Approval
→ Disbursement
→ Financial Realization
→ Accountability (LPJ)
→ Reimbursement
→ Reporting

## Key Features

- Fund request management
- Multi-stage approval workflow
- Budget ceiling validation
- Role-based access control
- Project and area-based data scoping
- Multi-role user access
- Audit trail
- In-app notifications
- Supporting document uploads
- PDF document generation
- Financial dashboard
- Project and budget management
- Database backup command
- Automated validation and business rules

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 11 |
| Frontend | Blade, Bootstrap 5, Vite, Vanilla JavaScript |
| Database | MySQL |
| PDF | barryvdh/laravel-dompdf |
| Authentication | Laravel Session Authentication |
| Testing | PHPUnit |
| Dependency Management | Composer |
| Frontend Build | Vite / npm |

## Architecture

The project follows Laravel's MVC architecture and separates application responsibilities through:

- Controllers
- Models
- Form Requests
- Middleware
- Services
- Observers
- Custom Validation Rules
- Value Objects
- Database Migrations
- Seeders
- Feature Tests
- Unit Tests

Business logic is handled through application services and validation rules to keep controllers focused on request handling.

## Security & Access Control

The application implements several security-related mechanisms:

- Session-based authentication
- Password hashing
- CSRF protection
- Role-based authorization
- Project/area data scoping
- Form request validation
- File upload validation
- Audit logging
- Session regeneration
- Budget validation with database locking

Sensitive configuration values are excluded from version control through `.gitignore`.

## Testing

The project contains automated feature and unit tests covering areas such as:

- Authentication
- Fund request creation
- Approval workflow
- Budget validation
- Project scoping
- Reimbursement and LPJ flow
- Audit logging
- Notifications
- File uploads
- System settings
- Business validation rules

Run the test suite with:

```bash
php artisan test
```

## Local Installation

### Requirements

- PHP 8.2+
- Composer
- MySQL
- Node.js & npm
- Git

### Setup

Clone the repository:

```bash
git clone https://github.com/adrian-satria/cdb-finance.git
cd cdb-finance
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database in `.env`, then run:

```bash
php artisan migrate --seed
```

Build frontend assets:

```bash
npm run build
```

Start the development server:

```bash
php artisan serve
```

## Demo Data

The repository includes sanitized demonstration data for portfolio purposes.

No production database, credentials, personal information, or confidential organizational data are included in this repository.



## AI-Assisted Development

AI-assisted software development tools were used during development to accelerate implementation, debugging, refactoring, testing, and documentation.

The application itself should not be considered an AI-powered financial system. AI was used as a development assistance tool.

## Portfolio Note

This project demonstrates practical experience in:

- IT application development
- Business process digitization
- Financial workflow automation
- Database-driven application development
- Role-based access control
- System validation
- IT operations and maintenance
- Troubleshooting and problem solvingAuthor

## Author

**Adrian Satria Putra**

IT Support & Application Development