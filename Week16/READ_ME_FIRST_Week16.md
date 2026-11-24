# Enrollment Requirement Validator

**Project 23 | Week 16 Performance, Reliability and Deployment Evaluation**  
**Sam Russell M. So | BSIT-3A | University of La Salette**  
Prepared and verified on 18 November 2026, Asia/Manila.

## Completed Week 16 system and evaluation

The Laravel/Flutter enrollment validator continues the student submission, staff correction, resubmission and verification workflow. Week 16 adds public minimal liveness/readiness endpoints, a staff service-readiness dialog, validated request trace IDs, safe request timing logs, a review-queue index, SQLite busy timeout/WAL configuration, an isolated workload evaluator, environment comparison, and a failed-configuration rollback drill.

**Recommendation: conditional use for a supervised fictional local demonstration only. The measured configuration is NOT approved for real enrollment or public peak-load commitments.** All eight measured runs had zero unexpected request errors, but the health-latency target failed in all eight runs, and peak throughput/latency results were not consistently within the pre-set goals. This is a completed evaluation, not a claim that every readiness gate passed.

## Measured findings

- Eight runs: ordinary 2-user and peak 8-user closed-loop workloads, repeated twice in development and twice in the controlled HTTPS environment.
- Data set: 200 fictional students and 600 requirements. This is not a 200-concurrent-user test.
- 320 measured application requests; 0 unexpected HTTP/transport failures. Warmup and monitoring probes are excluded from application throughput.
- Backend CPU: 83.0-89.5% of one logical-core equivalent during sampled windows. This is not host-wide CPU utilization.
- Maximum sampled PHP working set: 47.9 MiB. This is sampled RSS, not PHP heap or a guaranteed memory ceiling.
- Rollback drill: candidate liveness 200/readiness 503; old configuration and a stopped-server database checkpoint restored. Readiness recovered in 1.179 seconds; the authenticated checklist returned 200 and the selected checkpoint record was preserved.
- Regression: 30 Laravel tests / 106 assertions; 21 Flutter tests; 46 verified-HTTPS Postman/Newman requests / 144 assertions. Static analysis and the debug build passed.

## Package structure

| Folder | Contents |
|---|---|
| 0 Registration (private) | Project 23 registration and approval request for Sam Russell M. So, BSIT-3A; instructor approval/date blank |
| 01_Report | Three completed reports in PDF and editable Markdown, measured-results CSV, traceability, deployment runbook and readiness recommendation |
| 02_Evidence | Actual Week 16 app screenshots, plots from raw measurements, a clearly labeled data-derived evaluation dashboard and evidence gallery |
| 03_Logs | Per-request timings, health probes, CPU/RSS samples, environment/release records, rollback proof, regression and Postman logs |
| 04_Source_Code | Laravel/Flutter source, tests, VS Code workspace, setup, health probes and the isolated evaluation harness |

Open **02_Evidence/index.html**, **01_Report/2_Performance_and_Reliability_Results.pdf**, and **01_Report/3_Deployment_Recovery_and_Readiness.pdf** first.

## Run in VS Code

Open `04_Source_Code/EnrollmentValidator.code-workspace`. Run `Setup-Demo.ps1` from its terminal. Start the Laravel and Flutter workspace tasks in separate terminals. API: `http://127.0.0.1:8046/api`; frontend: port `5046`. The local debug client may use HTTP. The evaluated /api/health/* and protected API routes require HTTPS in production.

Fictional local student: `student.demo@example.test` / `StudentDemo-2026!`  
Fictional local staff: `staff.demo@example.test` / `StaffDemo-2026!`

These are deliberately public demonstration credentials, not deployment secrets. Setup generates the local private key and database. Student names in the UI are fictional; the author/preparer is Sam Russell M. So.

## Repeat verification and measurements

Run `php artisan test` in enrollment_validator_api and `flutter test` / `flutter analyze` in 04_Source_Code. The Postman collection is `postman/Week16_Enrollment_Evaluation.postman_collection.json`; its ordered workflow expects a freshly seeded disposable demo database. See the setup guide before any reset.

For the Windows evaluation harness, after installing dependencies, run:

```powershell
tools/Run-Week16-Evaluation.ps1
```

It creates NEW isolated databases and private runtime files, uses ports 8046, 8047 and 8447, and never resets the application's demo database. Stop conflicting listeners first. It needs PHP, Node.js, Windows PowerShell, and OpenSSL; set PHP_BIN / OPENSSL_BIN / OPENSSL_CONF if installed elsewhere. Close unrelated heavy work before measuring. New output is kept in an ignored runtime results directory. Exit code zero means the measurement/drill completed; inspect the individual gates before judging readiness.

## Important scope

Both compared environments run on the same Windows computer using one PHP built-in server worker. The controlled environment adds production flags, cached configuration/routes, and a local HTTPS proxy with strict certificate verification. It is a deployment surrogate, not a production FPM/web-server deployment or an Internet benchmark. There is no OPcache extension in the measured PHP runtime. Login hashing is outside the load window; tokens were issued to fictional fixtures in advance.

The rollback is a configuration rollback and point-in-time SQLite restore while the backend is stopped. It does not prove schema rollback, multi-host failover, zero production data loss, or a backup of concurrent real traffic. The system still does not upload scanned documents, run a reminder scheduler, or provide public registration/password recovery. Weeks 17-18 and institutional UAT are outside this submission.

No Week 15 screenshots or performance numbers were reused as Week 16 measurements. Registration documents request review and contain no fabricated instructor signature. Source preparation, tests and measurements were assisted by Codex; review and reproduce them before presentation.
