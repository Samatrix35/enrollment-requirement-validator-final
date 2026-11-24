# Enrollment Requirement Validator source

This is the authoritative Week 14 Flutter Web and Laravel 12 project for Sam Russell M. So, BSIT 3A, Project 23.

Open `EnrollmentValidator.code-workspace` in VS Code. Run `Setup-Demo.ps1` from this directory, then use the **Start Laravel API** and **Start Flutter Web** workspace tasks in separate terminals.

The API uses port 8024 and the frontend port 5024. The setup script installs the lockfile dependencies, prepares `.env`, generates a local key, and migrates and seeds fictional records without erasing an existing database.

Demo student: `student.demo@example.test` / `StudentDemo-2026!`  
Demo staff: `staff.demo@example.test` / `StaffDemo-2026!`

These credentials are for fictional local demonstration only.

The parent [README](../README.md), [setup guide](../01_Report/WEEK14_SETUP.md), [security report](../01_Report/WEEK14_SECURITY.md), and [evidence gallery](../02_Evidence/index.html) describe the completed slice and its limits.

The `postman/` directory contains the ordered API collection. Run `php artisan test` inside `enrollment_validator_api`. Run `flutter analyze` and `flutter test` here.

Requirement submission changes checklist state and does not upload scanned documents. Reminder scheduling and production deployment are outside this Week 14 slice.
