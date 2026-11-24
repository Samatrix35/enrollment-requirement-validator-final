# Enrollment Requirement Validator API

Project 23, Week 16. Prepared for Sam Russell M. So, BSIT-3A.

Laravel 12 / Sanctum / SQLite back the student checklist and staff review workflow. Use the source-root Setup-Demo.ps1 and VS Code workspace tasks. Local API port: 8046.

## Operational endpoints

- GET /api/health/live: minimal application liveness and release identity.
- GET /api/health/ready: database/schema readiness; 503 on dependency failure without private diagnostics.
- Both API health endpoints enforce HTTPS in production. The inherited /up is basic framework liveness, not the evaluated readiness gate.

Responses carry a validated or generated X-Request-ID. Server-Timing is middleware application duration, not full client latency. Operations logs contain route templates, status, duration and PHP peak heap bytes, without credentials, tokens, bodies or query values. PHP heap is different from sampled Windows working set.

The Week 16 migration adds a submitted-review queue index. SQLite busy timeout and WAL are configured; the single-worker evaluation does not prove multi-writer behavior.

Run php artisan test for regression and operations checks. Captured result: 30 tests / 106 assertions. Parent tools provide isolated workloads, monitoring and HTTPS checks. Read the reports for failed performance gates and the conditional local-only recommendation.

Generated configuration caches may contain secrets and must not be committed. The evaluator uses process-level environments and explicit trusted proxies. Do not use the public fictional demo credentials or PHP built-in server for real enrollment.
