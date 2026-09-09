06 — Installation Guide

6.1 Requirements

Before installing the application, make sure the following software is available:

PHP 8.2 or newer

Composer

MySQL

Node.js and npm

Git

A local PHP development environment such as Laragon, XAMPP, or equivalent

Recommended PHP extensions:

OpenSSL

PDO

PDO_MySQL

Mbstring

Tokenizer

XML

Ctype

JSON

Fileinfo

6.2 Clone the Repository

Clone the repository to your local development environment:

git clone <repository-url>
cd cdb-finance

6.3 Install PHP Dependencies

Install Laravel dependencies using Composer:

composer install

6.4 Configure Environment

Create a local environment file from the example configuration:

cp .env.example .env

On Windows PowerShell, you can also use:

Copy-Item .env.example .env

Generate the Laravel application key:

php artisan key:generate

Review the .env file and configure the local database connection.

Example:

APP_NAME="Finance Management Demo"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_management_demo
DB_USERNAME=root
DB_PASSWORD=

The database name, username, and password above are examples for a local development environment. Do not commit a real .env file or production credentials to the repository.

6.5 Create the Database

Create an empty MySQL database for local development.

Example database name:

finance_management_demo

The database name is only an example and can be changed according to your local environment.

6.6 Run Database Migrations

Run the Laravel migrations:

php artisan migrate

For a fresh local installation with demo data:

php artisan migrate:fresh --seed

migrate:fresh deletes existing tables and data in the configured database. Only use it with a dedicated local/demo database.

6.7 Seed Demo Data

The repository includes seeders for demonstration purposes.

Run:

php artisan db:seed

The demo environment provides sample:

Projects

Budget records

User account data

System configuration

The seed data is intentionally generic and does not represent production financial records.

6.8 Install Frontend Dependencies

Install Node.js dependencies:

npm install

Start the Vite development server:

npm run dev

For production assets:

npm run build

6.9 Start the Application

Start the Laravel development server:

php artisan serve

The application will normally be available at:

http://127.0.0.1:8000

If using Laragon or another local development environment, the application may also be accessible through the configured local virtual host.

6.10 Authentication

The application uses Laravel session-based authentication.

Demo user accounts should be created through the provided seeders or local development setup.

For security reasons:

Do not use production credentials in local seeders.

Do not commit real passwords.

Do not commit .env.

Change any default/demo credentials before using the application outside a local environment.

6.11 File Storage

If the application uses public file storage, create the symbolic link:

php artisan storage:link

Uploaded files should be stored according to the application's configured filesystem.

Do not place sensitive production documents inside the repository.

6.12 Clear Application Cache

If configuration or application changes are not immediately visible, clear the Laravel caches:

php artisan optimize:clear

You can also rebuild the application caches when required:

php artisan optimize

6.13 Run Tests

Run the automated test suite:

php artisan test

For a more detailed test output:

php artisan test --verbose

The repository contains feature and unit tests covering selected application workflows, validation, authorization, audit functionality, and business rules.

6.14 Development Workflow

A typical local development workflow is:

1. Clone repository
       ↓
2. composer install
       ↓
3. Configure .env
       ↓
4. php artisan key:generate
       ↓
5. Create local MySQL database
       ↓
6. php artisan migrate:fresh --seed
       ↓
7. npm install
       ↓
8. npm run dev
       ↓
9. php artisan serve
       ↓
10. Open application in browser

6.15 Security Notes

Before deploying an application outside a local development environment:

Set APP_ENV=production.

Set APP_DEBUG=false.

Use a strong application key.

Use secure database credentials.

Configure HTTPS.

Review file upload permissions.

Review authentication and authorization settings.

Do not expose .env.

Do not commit database dumps or production backups.

Do not commit real financial or personal data.

Review application logs before sharing the project publicly.

6.16 Portfolio / Demo Notes

This repository is provided as a portfolio and demonstration project.

The demo environment uses generic project names, budget categories, and sample data so that the repository can be shared without exposing organizational or production information.

The application architecture and workflow are based on a real-world financial management use case, while the publicly shared demo data and configuration have been sanitized for portfolio purposes.