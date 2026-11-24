# Week 14 setup and demonstration

## Requirements

PHP 8.2 or newer with SQLite, Composer, Flutter supporting Dart 3.13.7, Chrome, and VS Code. Postman is optional for opening the supplied collection. Composer dependencies and generated Flutter caches are omitted from the clean ZIP; setup restores them using the lockfiles.

## Installation

1. Extract the entire package to a writable local folder.
2. Open the workspace in 04_Source_Code using VS Code.
3. Run .\Setup-Demo.ps1 from 04_Source_Code. It installs packages, creates .env if absent, generates the key if missing, creates database.sqlite if absent, and migrates/seeds fictional records. It does not erase an existing database.
4. Start Laravel on port 8024 and Flutter on port 5024 using the workspace tasks. If a port is occupied, stop that development server or choose another port and update API_BASE_URL and FRONTEND_URLS together.

## Demonstration sequence

Sign in as the demo student and submit Birth Certificate. Sign out. Sign in as staff, review the queue, request a correction with a note, then sign out. The student can read the note and resubmit. Staff approve the resubmission. The approved count increases only after approval. Other requirements may remain pending; do not describe the entire enrollment as approved until all checklist items are approved.

## Postman

Import 04_Source_Code/postman/Week14_Enrollment.postman_collection.json. It contains 26 ordered requests with status assertions and login token capture. The base_url variable is local HTTP on port 8024. The saved API checks ran with Node fetch and the collection independently passed through Postman Newman (26 requests and 26 assertions). For the full collection, use a fresh fictional database: the scenario expects seeded requirement IDs 1, 2, 3. Only reset a disposable demo database; preserve any existing data first. The collection captures IDs for records it creates.

## Repeat verification

Run php artisan test inside enrollment_validator_api. Run flutter analyze and flutter test in 04_Source_Code. A debug build can target local HTTP. A release build must use an HTTPS API endpoint. The captured Laravel test run used a documented Windows sandbox readability harness; see 03_Logs/VERIFICATION_NOTES.md.

## Production boundary

Use trusted HTTPS, APP_ENV=production, APP_DEBUG=false, a narrow FRONTEND_URLS allowlist, and only real trusted proxy addresses. Do not expose the demonstration credentials or php artisan serve publicly. This package does not include a production certificate or claim a successful deployed HTTPS test.

To repeat the Postman run after installing Newman, use:

```powershell
newman run postman/Week14_Enrollment.postman_collection.json
```

Run this from 04_Source_Code against the fresh fictional database described above.
