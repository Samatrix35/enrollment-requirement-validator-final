# Week 15 setup and recovery guide

Open the source workspace in VS Code and run Setup-Demo.ps1. Required tools are PHP 8.2+ with SQLite, Composer, compatible Flutter/Dart, Chrome and optionally Postman/Newman. Composer.lock and pubspec.lock pin the tested dependencies. On a fresh machine, setup downloads dependencies, creates private .env configuration, generates APP_KEY, creates the SQLite file, migrates and seeds local-only fictional accounts.

Start Laravel on 127.0.0.1:8035. Start Flutter Web on port 5035 with API_BASE_URL=http://127.0.0.1:8035/api. Configure FRONTEND_URLS with the exact frontend origin. Restart processes after changing .env or build defines.

```powershell
# Terminal 1 from 04_Source_Code
cd enrollment_validator_api
php artisan serve --host=127.0.0.1 --port=8035

# Terminal 2 from 04_Source_Code
flutter run -d chrome --web-port=5035 --dart-define=API_BASE_URL=http://127.0.0.1:8035/api
```

## Demonstration

Sign in as student, submit a pending requirement, and confirm that only its Submitted state changes. Sign out and sign in as staff. Return it with a correction note. The student reads the feedback and resubmits; staff approve it. The student sees approval persisted and the approved count increases. Disable connectivity in a controlled demo, refresh, then restore connectivity and use Try again. No write request is silently retried.

## Tests and a disposable Postman database

Run the feature and Flutter tests from the README. For an ordered full Postman collection, first back up any local data you need. Only in a disposable local demo database, run `php artisan migrate:fresh --seed`, then import Week15_Enrollment_IA6.postman_collection.json. This reset deletes that demo database's tables; setup never performs it automatically. Collection variables capture newly created student/requirement IDs. IDs 1–3 refer to the seeded checklist.

## Upgrade and rollback

The new unique index prevents duplicate names for the same student. For an existing Week 14 database, inspect duplicate `(student_id, requirement_name)` groups before applying the migration; resolve them manually with an authorized reviewer and a backup. The migration intentionally fails rather than deleting or merging records silently. Name uniqueness uses the database's comparison rules; semantic and case-normalized duplicate detection remains a future enhancement.

Retain the original Week 14 ZIP and a database backup. To roll back the local prototype, stop the Week 15 processes and restore the matching Week 14 source, lockfiles and backup together. Do not mix database schema versions or assume `migrate:rollback` recovers changed business data.

## HTTPS evidence and deployment

The captured HTTPS tests used Node TLS termination on localhost:8445, an explicit local test certificate, and a PHP backend reachable only on loopback. Production Laravel trusted only the loopback proxy and received X-Forwarded-Proto=https from it. Newman verified the certificate using sslExtraCaCerts; insecure mode was false. Private test keys are excluded. The public test certificate is evidence only and expires; generate a fresh local certificate when reproducing. No OS/browser certificate store was modified. A public deployment still needs trusted public TLS, private credentials, APP_DEBUG=false, reviewed proxies, backups and monitoring.

## Reproduce verified local HTTPS checks

Install Node.js, npm and OpenSSL in addition to the application prerequisites. After setup, with disposable local data only, run `tools/Run-HTTPS-Checks.ps1 -ResetDemo` from 04_Source_Code. It installs the pinned Newman version, generates a fresh two-day test certificate, resets the fictional database, and runs the collection with explicit CA trust. It binds a loopback PHP backend on 8036 and TLS on 8445. The reset is gated by the explicit switch. Certificates and private keys stay in tools/certs and must not be committed. Stop any competing listeners on those local ports first.
