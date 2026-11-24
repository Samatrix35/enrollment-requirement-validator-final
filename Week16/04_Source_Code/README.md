# Enrollment Requirement Validator Week 16 source

Project 23. Sam Russell M. So, BSIT-3A. Open EnrollmentValidator.code-workspace in VS Code, run Setup-Demo.ps1, then start the Laravel and Flutter tasks (8046 / 5046).

The parent README and 01_Report/WEEK16_SETUP_AND_REPRODUCTION.md explain measured results, targets, limits and reproduction. tools/Run-Week16-Evaluation.ps1 creates isolated fixtures and runs the workload/environment/recovery evaluation; it does not reset the demo application database. It requires Windows process counters, PHP, Node and OpenSSL.

New application features: /api/health/live, /api/health/ready, the staff service-health dialog, safe trace/timing logs, a submitted-queue index and SQLite WAL/busy timeout settings. The inherited student/staff workflow and security tests remain.

Use php artisan test in enrollment_validator_api and flutter test / flutter analyze here. Postman collection: postman/Week16_Enrollment_Evaluation.postman_collection.json. Successful execution of an evaluation is not automatic approval of all performance gates. Review the conditional recommendation before presenting deployment readiness.
