# Enrollment Requirement Validator Week 15 source

Project 23, IA6. Sam Russell M. So, BSIT-3A.

Open EnrollmentValidator.code-workspace in VS Code. Run Setup-Demo.ps1, then start the Laravel and Flutter workspace tasks. Ports: API 8035; Flutter 5035.

The parent README and 01_Report/WEEK15_SETUP.md explain credentials, tests, database upgrades, demonstration, and limits. The postman directory contains the Week 15 collection. `php artisan test` runs the backend suite; `flutter test` includes API and screen/state tests.

Use fictional local data only. Setup restores dependencies and creates private local runtime configuration. It does not erase existing data. The new composite unique index requires duplicate-free existing student/requirement pairs before migration.
