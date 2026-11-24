# Enrollment Requirement Validator

Individual project for PFEBSIT 002 (Weeks 13–18), Project 23.

The project is a Flutter Web enrollment checklist with a Laravel 12 API and SQLite database. Week 14 adds signed-in student and enrollment-staff flows, server-side role and ownership checks, protected API configuration, input validation, minimized responses, and security evidence. Development uses fictional records only.

## Project Information

- Student: Sam Russell M. So
- Program/Section: BSIT – 3A
- School: University of La Salette
- Frontend: Flutter Web
- Backend: Laravel 12 with Sanctum
- Database: SQLite
- API checks: Laravel feature tests and Postman scenarios
- Development tools: Visual Studio Code, Flutter, PHP, Composer

## Week 14 Changes

- Laravel Sanctum issues 60-minute personal access tokens; logout revokes the active token.
- User accounts have `student` or `enrollment_staff` roles. Laravel middleware enforces roles on the server.
- A student account is linked to one student record. Students can see and submit requirements for their linked record only.
- Only enrollment staff can list, create, update, or delete student and requirement records, and only staff can approve or return a submission.
- Students cannot set a verification status. They can submit a pending or returned requirement for staff review.
- Passwords use Laravel's hashed password cast. Authentication failure responses do not disclose whether an email exists.
- The Flutter client keeps the access token in memory only, clears it on sign-out or a 401 response, and requires HTTPS for release builds.
- Laravel limits sign-in attempts, validates request fields on the server, and returns minimized student and requirement data.
- API logs record event names and record IDs, not passwords, tokens, or request bodies.
- Production responses add security headers; production API requests require HTTPS. The HTTPS guard respects only explicitly configured trusted proxy addresses.

The complete control map, Postman scenarios, local test results, and remaining risks are in [`evidence/WEEK14_SECURITY_EVIDENCE.md`](evidence/WEEK14_SECURITY_EVIDENCE.md).

## Current Feature Status

| Feature | Status |
| --- | --- |
| Flutter Web project and responsive checklist | Completed |
| Student/staff sign-in and role-specific screens | Implemented for Week 14 |
| Laravel Sanctum authentication and token expiry | Implemented for Week 14 |
| Server-side role authorization and student ownership | Implemented for Week 14 |
| Requirement submission and staff decision workflow | Implemented for Week 14 |
| Protected configuration and HTTPS enforcement | Implemented; deployment TLS configuration remains environment-specific |
| Server-side input validation and safe event logging | Implemented |
| Minimized API responses | Implemented |
| Week 14 adverse security scenarios | Laravel feature tests passed; Postman scenarios are documented for manual demonstration |
| Automated reminders and bounded retries | Week 13 work remains in progress |
| Broader testing and hardening | Week 15 |
| Performance, deployment evaluation, UAT, and defense | Weeks 16–18 |

## Repository Structure

```text
├── enrollment_validator_api/   # Laravel API, migrations, feature tests
├── evidence/                   # Week 13/14 evidence and security notes
├── lib/                        # Flutter client and in-memory auth controller
├── test/                       # Flutter widget test
├── web/                        # Flutter Web entry point
├── pubspec.yaml
└── README.md
```

## Run Locally

### 1. Configure the Laravel API

Open a terminal in `enrollment_validator_api`:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Edit the local `.env` file and set two different private demo passwords, each at least 12 characters:

```dotenv
DEMO_STUDENT_PASSWORD=use-a-private-local-student-password
DEMO_STAFF_PASSWORD=use-a-different-private-staff-password
FRONTEND_URLS=http://localhost:5000
SANCTUM_EXPIRATION=60
```

Then create the SQLite database tables and fictional demo accounts:

```powershell
php artisan migrate --seed
php artisan serve
```

The local development API is `http://127.0.0.1:8000`. This HTTP address is only for local development. Do not use it for real student information or a public deployment. In production, configure a trusted HTTPS endpoint, set `APP_ENV=production` and `APP_DEBUG=false`, set `APP_URL` to HTTPS, and configure `TRUSTED_PROXIES` with the actual proxy address if TLS terminates at a reverse proxy.

Local demo sign-in names are `student.demo@example.test` and `staff.demo@example.test`; the passwords are the values you set in your local `.env`. These demo accounts are seeded only in `local` or `testing` environments and are not stored in the repository.

### 2. Run the Flutter Web client

Open a second terminal at the repository root:

```powershell
flutter pub get
flutter run -d chrome --web-port=5000 --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
```

The local client uses HTTP only because `php artisan serve` is local development. A release build rejects an HTTP API URL:

```powershell
flutter build web --dart-define=API_BASE_URL=https://your-api-host.example/api
```

Use a trusted certificate for any deployed API. Do not commit `.env`, database files containing student records, access tokens, or real credentials. The backend `.gitignore` excludes `.env` and `vendor/`.

## API Overview

All routes except `POST /api/auth/login` require a Sanctum bearer token. API errors use standard HTTP statuses: `401` for missing or invalid authentication, `403` for a role denial, `404` when the signed-in student does not own the requested record, `409` for an invalid workflow transition, and `422` for invalid input.

| Method | Endpoint | Role | Purpose |
| --- | --- | --- | --- |
| POST | `/api/auth/login` | Public, throttled | Issue a short-lived token |
| GET | `/api/me` | Student or staff | Return the minimized signed-in profile |
| POST | `/api/auth/logout` | Student or staff | Revoke the active token |
| GET | `/api/my/enrollment` | Student | Retrieve only the linked student's checklist |
| PATCH | `/api/my/requirements/{id}/submit` | Student | Submit own pending/returned requirement |
| GET/POST/GET{id}/PUT/PATCH/DELETE | `/api/students` | Enrollment staff | Manage student records |
| GET/POST/GET{id}/PUT/PATCH/DELETE | `/api/requirements` | Enrollment staff | Manage and review requirements |

See `enrollment_validator_api/routes/api.php` for the registered route definitions.

## Development Milestones

| Week | Focus | Status |
| --- | --- | --- |
| Week 13 | Automation foundation | In progress |
| Week 14 | Secure integration | Implemented in this Week 14 copy; review the evidence and test results |
| Week 15 | Testing and hardening | Pending |
| Week 16 | Performance, reliability, and deployment evaluation | Pending |
| Week 17 | UAT, documentation, and evaluation | Pending |
| Week 18 | Final demonstration and technical defense | Pending |

## Known Limits

- The demo accounts and records are fictional and local-only. There is no public registration or password recovery flow.
- SQLite is suitable for this prototype, not a high-availability production enrollment system.
- `php artisan serve` does not provide HTTPS. Production TLS must be configured at the web server or trusted reverse proxy.
- This Week 14 slice does not implement the Week 13 reminder scheduler/retry workflow or Week 15 regression and hardening scope.
- Token storage in browser memory avoids persistence across reloads but does not protect against a compromised browser or cross-site scripting. A production deployment needs a reviewed content security policy and broader security assessment.

## Author

Sam Russell M. So  
BSIT – 3A  
University of La Salette
