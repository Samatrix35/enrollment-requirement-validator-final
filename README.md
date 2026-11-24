# Enrollment Requirement Validator

**Project 23 | Week 14 Secure Integration | PFEBSIT 002**  
Sam Russell M. So · BSIT 3A · University of La Salette  
Prepared and verified on 17 November 2026 (Asia/Manila).

## Completed Week 14 system

The working local prototype uses **Flutter Web, Laravel 12, Sanctum, and SQLite**. A student signs in, views only their linked enrollment checklist, submits requirements, reads correction feedback, and resubmits. Enrollment staff sign in to review submitted items, approve them, or return them with a required correction note. Staff CRUD endpoints manage student and requirement records.

The interface now uses a consistent teal theme and status colors. The progress bar counts **approved** requirements, so submitting an item does not incorrectly mark enrollment as complete. The correction dialog waits for its close animation before releasing its text controller. Student and staff refresh callbacks were fixed to avoid returning a network Future from setState.

## Open these first

| Folder | Contents |
|---|---|
| 01_Report | Finished report, setup guide, security notes, evidence index, original project specification |
| 02_Evidence | Fresh screenshots of the running Flutter application; captioned evidence gallery |
| 03_Logs | Actual Laravel, Flutter, and live API verification results, security event excerpt, file hashes |
| 04_Source_Code | Authoritative Laravel and Flutter project, VS Code workspace, setup scripts, Postman collection |

Start with [the setup guide](01_Report/WEEK14_SETUP.md) and [the evidence gallery](02_Evidence/index.html).

## Verified results

- Laravel: **7 tests, 32 assertions passed**.
- Live API: **26 of 26 checks passed**, including 401/403/404 access controls, validation, workflow conflicts, corrections, approval, and logout revocation.
- Flutter static analysis: **No issues found**.
- Flutter widget test: **1 passed**.
- Flutter debug web build: **successful**; local HTTP is allowed in debug only.

Read the raw logs for the exact run. API tokens are redacted in saved responses. The Node HTTP runner performed the 26 live checks. The supplied Postman collection was also executed using Postman Newman: 26 requests and 26 assertions passed, with zero failures. The Newman logs and JUnit report are included.

## Run in VS Code

Open 04_Source_Code/EnrollmentValidator.code-workspace. In a PowerShell terminal:

~~~powershell
cd 04_Source_Code
.\Setup-Demo.ps1
~~~

Then run the **Start Laravel API** task and the **Start Flutter Web** task in separate VS Code terminals, or use:

~~~powershell
# Terminal 1, inside 04_Source_Code
cd enrollment_validator_api
php artisan serve --host=127.0.0.1 --port=8024

# Terminal 2, inside 04_Source_Code
flutter run -d chrome --web-port=5024 --dart-define=API_BASE_URL=http://127.0.0.1:8024/api
~~~

Local demo student: student.demo@example.test / StudentDemo-2026!  
Local demo staff: staff.demo@example.test / StaffDemo-2026!

These are intentionally published **fictional local demonstration credentials**. Do not reuse them for a public deployment. Setup creates a private application key and local SQLite database. Neither the key nor the database is included in the submission ZIP.

## Scope and remaining work

This submission completes the Week 14 local secure-integration prototype. It is not a deployed school service. Requirement submission records checklist state; scanned-document uploads, reminder scheduling/retries, public registration, password recovery, production TLS deployment, and Weeks 15–18 evaluation remain outside this slice. The browser token stays in memory and expires after 60 minutes. Production API requests require HTTPS and release clients reject HTTP API URLs.

Your original top-level Node prototype and earlier nested project are retained in the Downloads folder for reference. Use **04_Source_Code** for this finished Laravel submission. The friend's ZIP informed folder organization only; its topic, report, screenshots, and source were not copied.
