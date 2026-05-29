# Open SEIMS Documentation

**Special Education Information Management System**  
Version: 1.0 | License: GNU Affero General Public License v3.0

---

## Table of Contents

1. [Overview / Business Goal](#1-overview--business-goal)
2. [Architectural Diagrams](#2-architectural-diagrams)
3. [Technology Stack / Platform](#3-technology-stack--platform)
4. [Installation Guide](#4-installation-guide)
5. [User Guide](#5-user-guide)
6. [Source Code Reference](#6-source-code-reference)
7. [API Reference](#7-api-reference)
8. [Release Notes](#8-release-notes)
9. [Contributing Guide](#9-contributing-guide)

---

## 1. Overview / Business Goal

### What is Open SEIMS?

Open SEIMS (Special Education Information Management System) is an open-source, role-based web platform designed to support **ministries of education and implementing partners** in collecting, managing, visualizing, and using special education data for inclusive education planning, monitoring, and reporting.

### Problem Statement

Ministries of education in low- and middle-income countries often lack structured systems for tracking learners with special educational needs (SEN). Data is fragmented across paper registers, spreadsheets, and disconnected systems. This leads to:

- Poor visibility into inclusion outcomes at school, district, regional, and national levels
- Inability to generate reliable IEP (Individualized Education Program) compliance reports
- Gaps in teacher capacity data and assistive device inventory
- Challenges meeting international reporting obligations (e.g., SDG 4, CRPD)

### What Open SEIMS Does

The system provides a secure, structured foundation for:

| Feature | Description |
|---|---|
| **Learner Records** | Maintain comprehensive profiles for SEN learners including disability conditions, severity, and enrollment history |
| **IEP Management** | Create, track, and review Individualized Education Plans with goal entries, team members, and parental consent |
| **Attendance Tracking** | Record and report class attendance for SEN learners |
| **Teacher Profiles** | Capture teacher qualifications, SEN training, and classroom assignments |
| **School Management** | Manage school profiles, accessibility features, and officer assignments |
| **Supervision Reports** | Log field supervision visits, observations, and domain scores |
| **Assessments** | Link learners to assessment forms and record outcomes |
| **Assistive Devices** | Track device types, requests, fulfillment, and returns per learner |
| **Dashboard & Analytics** | National dashboard showing KPIs by reporting year with regional coverage |

### Target Users

| Role | Geography | Key Actions |
|---|---|---|
| National Admin | National | System configuration, user management, full data access |
| National SpED Officer | National | Read-only oversight of all data |
| Regional Education Director | Regional | Oversight, school management, report submission |
| District Officer | District | Monitoring, school oversight, field supervision |
| School Coordinator | School | Learner data entry, IEPs, attendance, teacher management |

### EMIS Integration

Open SEIMS is designed with interoperability in mind. It integrates with national **Education Management Information Systems (EMIS)** using the EMIS code as a school identifier, eliminating data silos between special education and mainstream education records.

### Value Added

- **Evidence-informed planning**: Aggregated dashboards enable data-driven policy decisions
- **Accountability**: Audit logs track all system changes with actor attribution
- **Privacy-first**: Role-based access, geographic scoping, and masked contact fields protect learner data
- **Scalable**: Configurable for different country contexts; designed as a reusable digital public good

---

## 2. Architectural Diagrams

### High-Level System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Browser / Client                          │
│            (Filament v5 Admin Panel — Livewire 4)               │
└──────────────────────────┬──────────────────────────────────────┘
                           │ HTTPS
┌──────────────────────────▼──────────────────────────────────────┐
│                    Laravel 12 Application                         │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │  HTTP Layer  │  │  Queue Worker│  │   Scheduler (Cron)   │   │
│  │  (Routes,   │  │  (Imports,   │  │  (Activity cleanup)  │   │
│  │  Middleware)│  │  Exports)    │  │                      │   │
│  └──────┬──────┘  └──────┬───────┘  └──────────────────────┘   │
│         │                │                                        │
│  ┌──────▼────────────────▼────────────────────────────────────┐ │
│  │                   Application Core                           │ │
│  │  ┌──────────────┐  ┌──────────────┐  ┌─────────────────┐  │ │
│  │  │  Filament    │  │   Models &   │  │    Services     │  │ │
│  │  │  Resources   │  │   Policies   │  │  (Dashboard,    │  │ │
│  │  │  (CRUD UI)   │  │  (Eloquent)  │  │   Reporting,    │  │ │
│  │  └──────────────┘  └──────────────┘  │   AuditLogger)  │  │ │
│  │                                       └─────────────────┘  │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │               External Integrations                          │ │
│  │  ┌──────────────┐  ┌─────────────────────────────────┐    │ │
│  │  │  Sanctum API │  │  KoboToolbox Webhook             │    │ │
│  │  │  (REST + JWT)│  │  (Incoming form submissions)     │    │ │
│  │  └──────────────┘  └─────────────────────────────────┘    │ │
│  └────────────────────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                      Data Layer                                   │
│  ┌─────────────────────┐  ┌───────────────┐  ┌──────────────┐  │
│  │  MySQL / MariaDB    │  │  File Storage  │  │ Cache / Queue│  │
│  │  (Primary Database) │  │  (S3 or local) │  │  (Database)  │  │
│  └─────────────────────┘  └───────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Core Domain Model

```
Region ──< District ──< School ──< Learner
                              │       ├──< LearnerCondition >── Condition
                              │       ├──< LearnerAccommodation >── AccommodationType
                              │       ├──< LearnerAssessmentHistory
                              │       ├──< DeviceLearner >── DeviceType
                              │       ├──< IepGoal
                              │       │     ├──< IepGoalEntry
                              │       │     │     └──< IepGoalEntryScore
                              │       │     └──< IepTeamMember >── User
                              │       └──< AttendanceRecord >── Teacher
                              │
                              ├──< Teacher >── User
                              └──< SupervisionReport >── User (supervisor)
                                    ├──< SupervisionObservation
                                    └──< SupervisionDomainScore

User ──< Officer ──>< School (via officer_school pivot)
```

### Two-Panel Filament Architecture

Open SEIMS uses two Filament panels:

| Panel | Path | Access |
|---|---|---|
| `admin` | `/admin` | `national_admin` role only — system configuration, user management, audit logs |
| `seims` | `/` (default) | All other roles — operational data entry and reporting |

### Geographic Access Control (Row-Level Security)

Every data query is automatically scoped by geography using Eloquent global scopes:

```
national_admin / national_sped_officer → full access (no scope)
regional_education_director           → region_id filter
district_officer                      → district_id filter
school_coordinator                    → school_id filter
```

This applies to `Learner`, `School`, and `SupervisionReport` models via dedicated `Scope` classes in `app/Models/Scopes/`.

---

## 3. Technology Stack / Platform

### Backend

| Component | Technology | Version |
|---|---|---|
| Language | PHP | ^8.3 |
| Framework | Laravel | ^12.0 |
| Admin UI | Filament | ^5.0 |
| Reactive UI | Livewire | ^4.1 |
| Authentication | Laravel Sanctum | ^4.0 |
| Roles & Permissions | Spatie Laravel Permission | ^6.0 |
| Activity Logging | Spatie Laravel Activitylog | ^4.12 |
| Media Library | Spatie Laravel Medialibrary | ^11.0 |
| API Documentation | Dedoc Scramble | ^0.13 |
| CSV Import/Export | Filament Actions (built-in) | — |
| Charts | Filament Apex Charts | ^5.0 |
| PDF Viewer | joaopaulolndev/filament-pdf-viewer | ^3.0 |

### Frontend

| Component | Technology | Version |
|---|---|---|
| CSS Framework | Tailwind CSS | ^4.1 |
| Build Tool | Vite | ^7.1 |
| HTTP Client | Axios | ^1.8 |
| Concurrency | concurrently (dev) | ^9.0 |

### Database

| Component | Details |
|---|---|
| Primary | MySQL 8+ or MariaDB 10.6+ (recommended) |
| Development | SQLite (supported via Laravel) |
| Cache Driver | Database (default) |
| Queue Driver | Database (default) |
| Session Driver | Database (default) |

### Infrastructure

| Component | Details |
|---|---|
| File Storage | Local disk or S3-compatible (configurable via `FILESYSTEM_DISK`) |
| Job Queue | Laravel Queue Worker (database driver) |
| Cron | Laravel Scheduler (1-minute cron) |
| Web Server | Nginx or Apache (with `public/` as document root) |

### Known Compatible Versions

- PHP 8.3.x ✅
- MySQL 8.0+ ✅
- Node.js 20.19+ / 22.12+ ✅ (required by Vite 7)

### Known Incompatibilities

- PHP < 8.2 ❌
- Node.js < 20.19 ❌ (laravel-vite-plugin 2.x requirement)

---

## 4. Installation Guide

### Prerequisites

- PHP 8.3+ with extensions: `ext-ctype`, `ext-filter`, `ext-hash`, `ext-mbstring`, `ext-openssl`, `ext-session`, `ext-tokenizer`, `ext-intl`, `ext-exif`, `ext-fileinfo`, `ext-gd`
- Composer 2.x
- Node.js 20.19+ and npm
- MySQL 8+ or MariaDB 10.6+
- A web server (Nginx recommended) or PHP's built-in server for development

---

### Local Development Setup

#### 1. Clone the Repository

```bash
git clone https://github.com/your-org/openseims.git
cd openseims
```

#### 2. Install PHP Dependencies

```bash
composer install
```

#### 3. Install Node.js Dependencies

```bash
npm install
```

#### 4. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your local settings:

```env
APP_NAME="Open SEIMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=openseims
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
FILESYSTEM_DISK=public

# Active reporting year (adjust as needed)
SEIMS_ACTIVE_REPORTING_YEAR=2025
```

#### 5. Create the Database and Run Migrations

```bash
mysql -u root -e "CREATE DATABASE openseims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

#### 6. Seed Reference Data

```bash
php artisan db:seed
```

This seeds regions, districts, schools, conditions, service types, device types, roles, permissions, and a default admin user.

#### 7. Create Storage Link

```bash
php artisan storage:link
```

#### 8. Build Frontend Assets

```bash
npm run build
# or for development with hot-reload:
npm run dev
```

#### 9. Start the Development Server

```bash
# All services at once:
composer dev

# Or individually:
php artisan serve
php artisan queue:listen --tries=1
```

#### 10. Default Login Credentials

After seeding, the following accounts are available:

| Role | Email | Password |
|---|---|---|
| National Admin | `national.admin@example.com` | `Pass1234` |
| District Officer | `district.officer@example.com` | `Pass1234` |
| School Coordinator | `school.coordinator@example.com` | `Pass1234` |

> ⚠️ **Change all default passwords immediately in any non-development environment.**

---

### Production Deployment

#### Environment Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example.com

# Use a production-grade cache/queue driver if possible
CACHE_STORE=database
QUEUE_CONNECTION=database

# S3 storage (recommended for production)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...
```

#### Deployment Steps

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
npm run build
php artisan storage:link
```

#### Queue Worker (systemd example)

```ini
[Unit]
Description=Open SEIMS Queue Worker
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/openseims
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always

[Install]
WantedBy=multi-user.target
```

#### Cron (Laravel Scheduler)

```bash
# Add to system crontab
* * * * * cd /var/www/openseims && php artisan schedule:run >> /dev/null 2>&1
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.example.com;
    root /var/www/openseims/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

### Configuration Reference

Key `.env` variables:

| Variable | Default | Description |
|---|---|---|
| `SEIMS_ACTIVE_REPORTING_YEAR` | `2026` | Default year shown in dashboards and reports |
| `SEIMS_IMPORTED_USER_DEFAULT_PASSWORD` | `Pass1234` | Password assigned to users created via CSV import |
| `SEIMS_SYNC_ROLE_PERMISSIONS_ON_SEED` | `false` | Re-sync permissions on every `db:seed` run |
| `ACTIVITY_LOGGER_ENABLED` | `true` | Enable/disable the audit log |
| `LOG_CHANNEL` | `stack` | Logging channel (`stack`, `daily`, `slack`, etc.) |

---

## 5. User Guide

### Navigating the Interface

After logging in, you will see a sidebar with sections tailored to your role:

- **Dashboard** — KPI summary cards by reporting year
- **Assessments** — Learner assessment records
- **Individual Education Plans** — IEP management
- **Supervision Reports / My Reports** — Field supervision
- **Lists** — Learners, Officers, Teachers, Schools, Attendance
- **Resources** — Assessment forms, Training modules, IE Policies

### Managing Learners

1. Navigate to **Lists → Learners**
2. Click **New Learner** to open the creation wizard (4 steps: Basic Info, Special Needs, Academic Notes, Contact & Needs)
3. Fill in required fields: name, date of birth, sex, school, enrollment date, status, class
4. Under **Special Needs**, add one or more disability conditions. Mark one as **Primary**
5. Optionally add accommodations under **Contact & Needs**
6. Click **Create Learner**

> 💡 **Tip**: Use **Upload CSV** to bulk-import learners. Download the template from the import dialog for the required column format.

### Creating an IEP

1. Navigate to **Individual Education Plans → New IEP**
2. The creation wizard has 4 steps:
   - **Basic Info**: Select learner, set start/end dates, goal type, and IEP team members
   - **Goals**: Set program placement, frequency, and add goal entries (instruction area, baseline %, target %)
   - **Services**: Select related services and associated assistive devices
   - **Documents**: Upload signed IEP PDF and capture parental consent
3. After creation, each section can be edited independently via the sub-navigation menu on the IEP record

### Recording Attendance

1. Navigate to **Lists → Attendance**
2. Click **New Attendance Record**
3. Select the teacher, class, and date
4. Add learners and mark each as present or absent (with reason if absent)
5. You can also **Upload CSV** for bulk entry

### Submitting a Supervision Report

1. Navigate to **Supervision Reports → New Report**
2. The wizard covers: School & Visit Details → Observations → Domain Scores
3. Each observation records an issue found, intervention provided, deadline, and resolved status
4. Domain scores can assess areas like "Inclusion Practices" on a 0–100 scale
5. Assign a **recipient** (the staff member responsible for acting on the report)

### Bulk CSV Imports

All major entities support bulk import via CSV. Each importer:

- Provides a downloadable template with example rows
- Validates all rows before committing (failed rows are saved to `failed_import_rows`)
- Shows a notification when complete

Available importers: **Learners, Teachers, Officers, Schools, Attendance Records, IEP Goals**

### Frequently Asked Questions

**Q: Why can't I see learners from another school?**  
A: Access is geographically scoped to your assigned school, district, or region. Contact your system administrator if you need broader access.

**Q: How do I reset a user's password?**  
A: National Admins can edit users at `/admin/users`. For self-service, use the "Forgot Password" link on the login page (requires mail to be configured).

**Q: Can I view the audit trail of changes?**  
A: National Admins can access **Admin → Audit Logs** in the `/admin` panel to see a full history of all create/update/delete actions.

**Q: How do I change the active reporting year on the dashboard?**  
A: Use the year selector on the Dashboard page. Your selection is saved per-user.

---

## 6. Source Code Reference

### Repository Structure

```
openseims/
├── app/
│   ├── Enums/                  # PHP 8.1 backed enums for all status/type fields
│   ├── Filament/
│   │   ├── Admin/              # Admin panel resources (Users, Officers, Roles, Audit)
│   │   │   └── Resources/
│   │   ├── Imports/            # Filament CSV importers for each entity
│   │   ├── Pages/              # Custom Filament pages (Dashboard, IE Policies)
│   │   └── Resources/          # Main SEIMS panel resources (Learners, IEPs, etc.)
│   │       └── [Entity]Resource/
│   │           ├── Pages/      # CRUD pages per resource
│   │           └── RelationManagers/
│   ├── Http/
│   │   ├── Controllers/        # Webhook controller (KoboToolbox)
│   │   └── Middleware/         # SetActiveReportingYear middleware
│   ├── Models/
│   │   ├── Concerns/           # Auditable trait (wraps Spatie Activitylog)
│   │   └── Scopes/             # Geographic Eloquent global scopes
│   ├── Policies/               # Laravel authorization policies per model
│   ├── Providers/
│   │   └── Filament/           # AdminPanelProvider, SeimsPanelProvider
│   ├── Services/
│   │   ├── Dashboard/          # NationalDashboardService (KPI computation)
│   │   └── Reporting/          # ActiveReportingYear service
│   └── Support/
│       ├── AuditLogger.php     # Manual audit logging helper
│       ├── GeographyData.php   # Ghana regions/districts seed data
│       ├── OfficerProvisioning.php # Creates officer + user account atomically
│       └── TeacherUserAccountManager.php # Creates teacher login for coordinators
├── database/
│   ├── factories/              # Model factories for testing/seeding
│   ├── migrations/             # Ordered database migrations
│   └── seeders/                # Reference data seeders
├── config/                     # Laravel + package configuration files
├── resources/
│   └── css/filament/seims/     # Filament theme overrides
├── routes/
│   ├── api.php                 # Sanctum API routes
│   └── web.php                 # Webhook route
└── ...
```

### Key Patterns

#### Enums (app/Enums/)

All status and type fields use PHP 8.1 backed enums implementing `Filament\Support\Contracts\HasLabel`. This ensures consistent labels across forms, tables, and imports.

Example: `LearnerStatus`, `DiagnosisStatus`, `GoalCompletionStatus`, `AccommodationStatus`

#### Geographic Scoping (app/Models/Scopes/)

Three Eloquent global scopes automatically filter queries based on the authenticated user's geographic assignment:

- `LearnerGeographicalScope` — filters `learners` by `school_id`, `district_id`, or `region_id`
- `SchoolGeographicalScope` — filters `schools`
- `SupervisionReportGeographicalScope` — filters `supervision_reports`

The scopes are bypassed when the user has `hasFullDataAccess()` (national roles).

#### Audit Logging (app/Models/Concerns/Auditable.php)

The `Auditable` trait wraps Spatie's `LogsActivity`. It enriches every activity log entry with:
- `module` — human-readable model name
- `subject_label` — display name of the changed record
- `subject_identifier` — EMIS code, email, or ID
- `actor_label` / `actor_email` — authenticated user details

Manual audit events (e.g., login, import completion) are logged via `AuditLogger::log()`.

#### CSV Importers (app/Filament/Imports/)

Each importer extends Filament's `Importer` class and defines:
- `getColumns()` — mapped CSV columns with validation rules
- `resolveRecord()` — returns a new model instance
- `afterCreate()` — handles related data (e.g., creating `LearnerCondition` after importing a learner)

The `NormalizesImportStrings` trait provides consistent string normalization.

#### IEP Sub-Navigation

The IEP resource uses Filament's record sub-navigation to split editing across 5 dedicated pages: Basic Info, Goals, Services, Documents, and Parental Consent. This improves usability for complex records.

---

## 7. API Reference

### Overview

Open SEIMS exposes a REST API powered by **Filament API Service** (rupadana/filament-api-service) with automatic OpenAPI documentation generated by **Scramble** (dedoc/scramble).

### API Documentation (Interactive)

Interactive API documentation is available at:

```
GET /docs/api
```

The raw OpenAPI spec is available at:

```
GET /docs/api.json
```

> Access requires authentication. Use the `/api/login` endpoint to obtain a token.

### Authentication

The API uses **Laravel Sanctum** token authentication.

#### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "national.admin@example.com",
  "password": "Pass1234"
}
```

**Response:**
```json
{
  "token": "1|abc123..."
}
```

Include the token in subsequent requests:

```http
Authorization: Bearer 1|abc123...
```

#### Get Authenticated User

```http
GET /api/user
Authorization: Bearer {token}
```

### Webhook Endpoint

Open SEIMS accepts incoming data from KoboToolbox form submissions:

```http
POST /webhook/kobo
Content-Type: application/json

{ ...kobo payload... }
```

The webhook is CSRF-exempt. Payloads are deduplicated using `event_id` and stored in `webhook_logs`.

### Rate Limiting

The API uses Laravel's default rate limiting. Refer to `config/app.php` and `RouteServiceProvider` for current limits.

### API Versioning

The current API version is configured in `config/scramble.php`:

```php
'version' => env('API_VERSION', '0.0.1'),
```

---

## 8. Release Notes

### Version 1.0.0 (Initial Release)

**Core Features:**
- Multi-panel Filament 5 application (Admin + SEIMS panels)
- Five-tier role system with geographic row-level security
- Full learner lifecycle management (enrollment, conditions, accommodations, talents)
- IEP management with multi-step wizard and sub-navigation editing
- Attendance recording (individual and bulk via CSV)
- Supervision report system with observations and domain scores
- Teacher and officer profiles with user account provisioning
- National dashboard with KPI cards and reporting year selector
- Audit logging for all create/update/delete operations, login events, and imports
- CSV import support for Learners, Teachers, Officers, Schools, Attendance, IEP Goals
- REST API with Sanctum authentication and auto-generated OpenAPI documentation
- KoboToolbox webhook integration
- Seeded reference data for Ghana (16 regions, 260+ districts)

**Technology baseline:**
- Laravel 12, PHP 8.3, Filament 5, Livewire 4, Tailwind CSS 4

---

*Future releases will follow [Semantic Versioning](https://semver.org/). A changelog will be maintained in `CHANGELOG.md` at the repository root.*

---

## 9. Contributing Guide

Thank you for your interest in contributing to Open SEIMS! This guide explains how to get involved.

### Code of Conduct

All contributors are expected to treat others with respect and maintain a welcoming environment. Harassment, discrimination, or abusive behaviour of any kind will not be tolerated.

### How to Contribute

#### Reporting Bugs

1. Check the **Issues** tab to ensure the bug has not already been reported
2. Open a new issue using the **Bug Report** template
3. Include: PHP/Laravel version, steps to reproduce, expected vs actual behaviour, and any relevant logs

#### Suggesting Features

1. Open a new issue using the **Feature Request** template
2. Describe the use case, target users, and how it aligns with Open SEIMS's mission of supporting inclusive education data

#### Pull Requests

1. Fork the repository and create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Follow the project's coding style:
   ```bash
   # PHP (Laravel Pint)
   ./vendor/bin/pint
   ```

3. Write or update tests as appropriate

4. Ensure migrations are reversible (implement `down()` methods)

5. Update seed data if you add new reference tables

6. Submit a pull request targeting the `main` branch with a clear description

### Development Workflow

```bash
# Run tests
composer test

# Run code style checks
./vendor/bin/pint --test

# Run all dev services (server + queue + logs + vite)
composer dev
```

### Coding Standards

- **PHP**: PSR-12, enforced by Laravel Pint
- **Enums**: All status/type fields must use backed PHP enums in `app/Enums/`
- **Authorization**: All new models must have a corresponding Policy in `app/Policies/`
- **Audit logging**: Models that store user-generated data should use the `Auditable` trait
- **Geographic scoping**: Models with user-visible data should apply appropriate global scopes
- **Imports**: New bulk-import features should use Filament's `Importer` class and follow the pattern in `app/Filament/Imports/`

### Adding a New Country Context

Open SEIMS is designed to be adapted for different country contexts:

1. Replace `app/Support/GeographyData.php` with your country's administrative hierarchy
2. Update the `RegionDistrictSeeder` or extend the seeder for your geography
3. Update `LearnerClass` enum values to match your education system levels
4. Review `SchoolLevel` enum for local school type classifications
5. Adjust the `SEIMS_ACTIVE_REPORTING_YEAR` config to match your reporting cycle

### Security Vulnerabilities

Please **do not** report security vulnerabilities in public GitHub issues. Instead, email the maintainers directly (see the repository's `SECURITY.md`). We will respond within 72 hours and coordinate a responsible disclosure.

### License

By contributing to Open SEIMS, you agree that your contributions will be licensed under the **GNU Affero General Public License v3.0** (AGPL-3.0). See `LICENSE` for the full text.

---

*This documentation was generated to meet [DPG Alliance documentation standards](https://github.com/DPGAlliance/publicgoods-candidates/blob/main/help-center/documentation.md).*
