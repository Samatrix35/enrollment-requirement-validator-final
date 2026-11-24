# Handover and Maintenance
Project23 | Week17 | Sam Russell M. So, BSIT-3A

# Setup and reproduction
## Prerequisites
Windows PowerShell, PHP8.2+ with SQLite/PDO, Composer2, Flutter stable with web, Chrome, Node18+ and npm. Tested host used PHP8.2.12 and Flutter3.47.8 (see actual logs for authoritative versions). Install from official tooling sources; lockfiles specify package versions.

## First run
1. Extract this package; open 04_Source_Code/EnrollmentValidator.code-workspace in VS Code if desired.
2. Run `powershell -ExecutionPolicy Bypass -File .\Setup-Demo.ps1` inside 04_Source_Code. It installs dependencies, makes framework directories, generates a private APP_KEY, migrates and seeds fictional demo accounts. It does not need any real university data.
3. In enrollment_validator_api run `php artisan serve --host=127.0.0.1 --port=8046`.
4. In a second source terminal run `flutter run -d chrome --web-hostname 127.0.0.1 --web-port 5046 --dart-define=API_BASE_URL=http://127.0.0.1:8046/api`.
5. Sign in as student.demo@example.test / StudentDemo-2026! or staff.demo@example.test / StaffDemo-2026! for fictional local use only. Never deploy these fixed demonstration credentials.

## Regression
Backend: `php artisan test` from API folder. Flutter: `flutter test`, `flutter analyze`, `flutter build web --debug --no-web-resources-cdn --dart-define=API_BASE_URL=http://127.0.0.1:8046/api`. For release use an approved HTTPS API and production TLS termination, not the local HTTP demo command.
Postman: import postman/Week17_Enrollment_Acceptance.postman_collection.json. Set base_url and temporary demo passwords locally. The scripted HTTPS runner under tools resets the generated demo DB only with explicit --reset-demo. Run `npm ci` for optional Node tools, then tools/Run-HTTPS-Checks.ps1 -ResetDemo. Read its documented reset behavior before use.
Browser: `node tools/run-week17-browser.cjs --reset-demo` after a debug web build; requires Chrome and optional Playwright1.62.1 (`npm install --no-save --package-lock=false playwright@1.62.1` after npm ci). It runs against the freshly seeded disposable demo DB, captures screenshots and records a workflow rehearsal; use only fictional data. Source tests and recorded results are separate from stakeholder feedback.

## Environment-specific execution notes
The automated authoring sandbox required a workspace copy of the Flutter SDK cache and a test-only PHP fopen readability adapter because ACL metadata probes failed. The adapter changes file-readability detection only, not assertions or application behavior. Normal local installations should use standard commands. No dependencies/private caches/build output or DB are distributed. See 03_Logs for exact commands/outcomes and any setup errors; do not count harness setup failures as product regressions.

## Offline-friendly local demonstration
Run Build-Local-Web.ps1, start the Laravel API, then run `node tools/serve-web.cjs` and visit http://127.0.0.1:5046. Bundling CanvasKit avoids the CDN dependency that failed during initial rehearsal. This debug HTTP setup is local-only. The automated browser runner includes --reset-demo explicitly because it reseeds its disposable demo database.


# User guide
Use fictional data only. Demo accounts/passwords are created by Setup-Demo.ps1 and are documented in its source for local practice only.

## Student
Sign in, view your checklist, select Submit for review on a Pending item. Submitted means awaiting staff, not approved. If Correction needed appears, read the correction note and resubmit after addressing it. Refresh status to obtain current results; approved count tracks staff-approved requirements only. Sign out when finished. This version records checklist status; it does not upload/validate document contents or issue institutional clearance.

## Enrollment staff
Sign in to see Submitted requirements. Approve only after the authorized manual verification, or select Request correction and supply a clear short note. Empty/whitespace notes show a required-field error. Send note returns the item to the student; it disappears from the submitted queue. Refresh queue for current state. Use Check service health for readiness; an unavailable message requires operator investigation, not repeated approvals.

## Errors and safe recovery
Invalid credentials never reveal protected data. Session-ended messages require sign-in again. For 409 refresh and inspect current status before repeating an action. For service unavailable check connection, then retry; do not assume a write failed until refreshing its outcome. Never share tokens, personal records or private configuration with screenshots. Use keyboard Tab to move focus and Enter/Space to activate focused controls; human accessibility review remains pending.


# Operations and maintenance
## Ownership
Student developer maintains the code; enrollment staff decide checklist outcomes; a designated operator owns deployment/backups; institutional authority approves real data/use. These roles do not imply named people have agreed to support the application.

## Daily/session checklist
Check /api/health/live (200 only proves process liveness) and /api/health/ready (200 requires DB/schema). Record release and request ID; investigate 503 without exposing paths or secrets. Review error rates, response time and DB/file capacity. Week 16 health p95 targets failed, so do not treat occasional green status as an availability SLA. Do not log passwords, access tokens, request bodies or student records. Restrict and rotate logs; choose retention with institution policy before real use.

## Backup/restore and incident response
For this isolated SQLite demo stop writers, run WAL checkpoint/truncate and copy the DB with release/config metadata to a private location; do not back up only the main file while uncheckpointed WAL contains writes. Prove restore into a separate environment, check schema/readiness, authenticated checklist and selected records. Treat DB backups as sensitive, never include them in coursework ZIPs. During an incident stop changes, record trace IDs/status, restore the known-good release/config and compatible stopped checkpoint, verify readiness/business data and document lost writes. Week 16 tested only controlled config rollback, not destructive schema rollback or continuous RPO0. Do not run rollback scripts against a real DB.

## Recurring maintenance
Before changes: create branch/backup, record requirement and acceptance criterion, review API compatibility. For each release: Composer audit, dependency review, backend/Flutter/API tests, analyze/build, controlled rollout and rollback verification. Monthly or when advisories appear: update dependencies in an isolated branch, rerun regression and inspect security configuration. Review tokens, CORS allowlist, TLS certificate validity and least-privilege DB/filesystem access. Never set trusted proxies broadly without a controlled proxy boundary. Use APP_DEBUG=false and private secrets in any approved deployment.

## Support and escalation
Student workflow issues -> enrollment staff role. 401 -> sign in again. 403 -> role review; never bypass. 409 -> refresh state. 422 -> correct input. 503/network -> operator checks dependencies/config then performs controlled recovery. Security/privacy issue -> institutional authority/operator; preserve minimal evidence and stop use of affected records. No real support contact or service commitment has been invented.

## Open work
Real stakeholder UAT, representative peak/soak/load tests, human accessibility review, approved production design, backup retention/restore policy and real support assignment remain pending. Reminder automation, file upload checking and public account lifecycle are out of scope.


# ADR-017 - Final architecture recommendation
Date: 2026-11-23 | Status: accepted for fictional local demonstration, deployment recommendation conditional.
Decision owner: student developer; institutional deployment authority is not assumed.

## Context
Project 23 needs two protected roles, transactional status changes, correction feedback and reproducible classroom evidence. Current measured host is a PHP development server on Windows with SQLite. No live university system, paid hosting or production administrator is authorized.

| Criterion | A: Laravel/SQLite local monolith | B: Laravel PHP-FPM/PostgreSQL behind TLS proxy |
|---|---|---|
| Functional fit | Existing implementation | Same API semantics, migration needed |
| Complexity/skills | Low, matches current student tools | Higher: proxy, process management, SQL server |
| Reliability | One host/worker; stopped-file recovery | Better separation possible; must engineer failover |
| Security | Roles and token controls; local HTTP demo | TLS and secret management required; more components |
| Cost | Existing local resources | Hosting, backups, monitoring and administration |
| Performance | Measured peak/health gaps | Concurrency options; not yet measured |
| Data ownership | Isolated fictional SQLite | Approved DB custody/retention and roles needed |
| Support/change | Simple lockfiles and local restore | Deployment/migration/on-call discipline required |

## Decision and consequences
Keep A for the bounded supervised demonstration. Evaluate B before real service. Avoid a microservice split: no independent domain/scale requirement justifies added network and ownership complexity. This comparison is an engineering judgement, not a benchmark of B. Revisit when real users, write concurrency, public access, backup obligations or multi-host availability become requirements. Exit gates: actual stakeholder UAT, approved data access, realistic load/soak tests, TLS/proxy trust verification, restore drill and assigned support owner.
