# School Management System

A comprehensive school management system built with Laravel 12 and a modular architecture (`nwidart/laravel-modules`). Supports the **Approve-First, Pay-Later** workflow with bilingual (English/Bangla) support.

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12
- **Modules:** nwidart/laravel-modules v12
- **RBAC:** spatie/laravel-permission
- **Auditing:** owen-it/laravel-auditing
- **PDF:** mpdf/mpdf
- **Excel:** rap2hpoutre/fast-excel
- **Frontend:** Vite, Alpine.js
- **Database:** MySQL

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Create database and update .env with credentials
# DB_DATABASE=school_management

# 5. Run migrations and seed
php artisan migrate
php artisan db:seed

# 6. Link storage
php artisan storage:link
```

## Development

```bash
# Run all services (server + queue + logs + vite)
composer dev

# Or individually:
php artisan serve
npm run dev
```

## Common Commands

```bash
# Database
php artisan migrate:fresh --seed       # Reset database
php artisan db:seed                    # Seed all data
php artisan db:seed --class=PermissionSeeder  # Seed permissions only

# Modules
php artisan module:list                # List all modules
php artisan module:make ModuleName     # Create new module
php artisan module:seed ModuleName     # Seed specific module
php artisan module:seed --class=ClassName ModuleName  # Run specific seeder

# Code Quality
./vendor/bin/pint                      # Format code (Laravel Pint)
php artisan test                       # Run tests

# Cache
php artisan optimize:clear             # Clear all caches

# IDE Helper
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan ide-helper:models
```

## Architecture

```
Modules/
├── ActivityLog/          # Audit trails (owen-it/laravel-auditing)
├── BackupCleanup/        # Database backup management
├── ImportDownloadManager/ # Excel import/export
├── Notification/         # Email & SMS notifications
├── PushNotification/     # Firebase push notifications
├── RolePermission/       # Roles & permissions (spatie)
├── Settings/             # System settings
├── User/                 # User management & profiles
├── Admission/            # (To be created) Application submission & review
├── Student/              # (To be created) Student profiles & enrollment
├── ClassManagement/      # (To be created) Classes, sections, schedules
└── FeeCollection/        # (To be created) Fee management & payment
```

## Key Workflows

1. **Admission → Student:** Application submitted → Admin reviews → Approved → Student record auto-created
2. **Fee Collection:** Student created → Fees initialized (pending) → Payment collected → Receipt generated
3. **Student ID Format:** `YYCCSSSSS` (Year + Class Code + Serial)

## Testing

```bash
php artisan test                       # Run all tests
php artisan test Modules/ModuleName/tests/  # Run module tests
```
