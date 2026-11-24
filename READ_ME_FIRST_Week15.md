# Enrollment Requirement Validator

**Project 23 | Week 15 Testing and Hardening (IA6)**  
**Sam Russell M. So | BSIT-3A | University of La Salette**  
Prepared and verified: 18 November 2026, Asia/Manila.

## Completed Week 15 work

The Laravel and Flutter enrollment prototype now includes a traceable test suite, recorded defects, regression fixes, real API/Postman checks, browser workflow evidence, local HTTPS verification, and dependency hardening. Student submission, staff correction, resubmission, approval, and explicit retry recovery remain the bounded workflow.

Eight findings were recorded: duplicate assignments, expiry handling before JSON parsing, diagnostic leakage on server errors, invalid response shape acceptance, queue pagination, login overflow, abandoned futures on session expiry, and dependency advisories. The source includes fixes and the logs preserve the observed failures and final passes. Additional hardening includes a whole-response timeout and conditional database updates for workflow transitions.

## Folder guide

| Folder | Contents |
|---|---|
| 0 Registration (private) | Project 23 registration and approval-request documents for Sam Russell M. So, BSIT-3A; instructor approval remains blank |
| 01_Report | Three completed reports in PDF and editable Markdown, traceability CSV, defect register, risks, setup and demonstration guide |
| 02_Evidence | Fresh application screenshots grouped into setup, backend, regression/recovery, screens, forms, final checks and negative tests; open index.html |
| 03_Logs | Before/after test results, JUnit, Postman Newman results, verified HTTPS record, dependency audit, browser record, manifest |
| 04_Source_Code | Laravel/Flutter source, tests, VS Code workspace/tasks, local setup and Postman collection |

The reports follow the reference document structure but all project content concerns Enrollment Requirement Validator. No friend's screenshots, logs, findings, student registry, or application code were adopted. The Week 14 enrollment archive supplied in this chat is the continuation baseline.

## Verified checks

- Laravel feature suite: **23 tests, 80 assertions passed**.
- Flutter: **19 tests passed**, covering API error handling, pagination, timeouts, screen states, submission, staff decisions, expiry and recovery.
- Postman Newman: **43 HTTPS requests, 130 assertions**, zero failures in the completed run.
- Flutter analysis: **no issues found**; debug web build completed.
- Browser: fresh end-to-end captures, responsive views, controlled offline recovery and server-side token revocation. Read browser-check.json for the final observed results.
- Composer audit after updates: no reported advisories or abandoned packages at the captured audit time. This is not a guarantee against unknown vulnerabilities.

## Run locally in VS Code

Open `04_Source_Code/EnrollmentValidator.code-workspace`. In its terminal:

```powershell
.\Setup-Demo.ps1
```

Use the **Start Laravel API** and **Start Flutter Web** tasks in separate terminals. They use ports **8035** and **5035**. Local debug HTTP is allowed. The independently recorded Postman run used a local TLS proxy and explicit test certificate trust; no browser or OS trust protections were disabled.

Fictional local student: `student.demo@example.test` / `StudentDemo-2026!`  
Fictional local staff: `staff.demo@example.test` / `StaffDemo-2026!`

These are demonstration credentials, not production secrets. Setup generates the private app key and local database. Neither is included in the clean submission ZIP. Composer dependencies, generated web files and Flutter caches are restored locally.

## Repeat the checks

```powershell
# In 04_Source_Code/enrollment_validator_api
php artisan test
composer audit --locked

# In 04_Source_Code
flutter analyze
flutter test
newman run postman/Week15_Enrollment_IA6.postman_collection.json
```

The ordered Postman collection expects a disposable freshly seeded database. It creates and removes fictional test records and exercises requirement IDs 1â€“3. Do not run it against real or valuable records. Setup itself does not erase an existing database. See WEEK15_SETUP.md before resetting a disposable demo.

## Boundaries

Week 15 does not complete Weeks 16â€“18. There are no scanned-document uploads, public registration, password recovery, reminder scheduler, or real school records. SQLite and the PHP development server remain local prototype infrastructure. Local HTTPS evidence uses an explicitly trusted test certificate and is not public deployment certification. Simulated client tests are labeled separately from live API and browser tests. A real multi-process concurrency stress test was not performed.

Registration documents request review; no instructor signature or approval date has been fabricated. Review all materials and be prepared to explain the implementation and tests. Development and preparation were assisted by Codex; do not represent automated runs as personally conducted classroom demonstrations.
