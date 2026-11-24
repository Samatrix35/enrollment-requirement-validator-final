# Final Integration Review
Project 23 - Enrollment Requirement Validator | Week 18
Sam Russell M. So | BSIT-3A | 23 November 2026

## Decision and scope
The bounded student-to-staff workflow is implemented in Flutter Web and Laravel/Sanctum. This portfolio supplies the system, fresh developer verification, real application captures and a defense/handover record. It supports a supervised fictional local demonstration. It does not establish real enrollment clearance, stakeholder acceptance, a passed examination or public production readiness.

## Problem and implemented workflow
Students need a current checklist of assigned requirements and understandable correction feedback. Staff need protected review decisions. A student signs in, views their own Pending items, submits an item, receives a staff correction note, resubmits and sees the staff-approved result. Staff can approve only Submitted items or return them with a nonblank correction note. Submission never automatically approves a requirement. A count of 1/3 approved means two items remain Pending; it does not mean the student is fully cleared for enrollment.

The UI covers login, own checklist, submission/resubmission, correction feedback, staff review queue, health feedback and sign-out. Staff student/requirement CRUD also exists in the API and Postman collection, but the Flutter slice is a checklist/review interface, not a complete registrar administration portal.

## Integration and trust boundary
Flutter sends JSON through ApiClient. AuthController retains identity and a bearer token in memory and chooses the role screen. The browser is untrusted: Laravel middleware checks authentication and role, and MyEnrollmentController checks ownership on every student submission. Controllers validate input before database updates. SQLite persists users, student records and requirements; Sanctum stores token hashes/expiry. CORS restricts browser origins but is not authorization. The application requires HTTPS in production; the fictional browser rehearsal uses loopback HTTP, while the API regression uses a verified local TLS certificate.

## Requirement-to-evidence chain
Final_Evidence_Map.csv links each requirement to source symbols, test artifacts, demonstration images and limitations. Fresh Week 18 results are in 03_Logs; the evidence gallery captions identify actual captures. Prior_Evidence contains only historical performance/rollback and Week 17 acceptance-status records needed to support the final review. Screenshots illustrate outcomes; server authorization, state conflicts and logout revocation are supported by tests/API responses rather than assumed from screen appearance.

## Security and state control
Sanctum issues expiring tokens; logout revokes the current token. Student ownership failures return 404 to avoid exposing other records; wrong role returns 403. Invalid inputs produce422, stale or disallowed transitions409, and production plaintext requests400. The client reacts to 401 by clearing local identity and returning to sign-in. Clearing local state after a network-failed logout cannot prove server-side revocation; let expiry/operator control handle that residual risk. A valid online logout is verified by the API regression.

Student Pending/Rejected -> Submitted clears old remarks. Staff Submitted -> Rejected requires a correction note <=500 characters; Submitted -> Approved requires staff authority. Conditional updates compare the current status in the database. Empty requirement updates return 422. The UI explains a blank correction note rather than silently ignoring it. Safe readiness messages omit database paths and exceptions; metric logs avoid credentials and request bodies.

## Architecture recommendation and alternatives
Architecture A, implemented: Flutter Web + Laravel modular monolith + SQLite on one local demonstration host. It is inexpensive and reproducible for this bounded classroom use, but has a single host/worker, SQLite write-contention and operational limitations. Architecture B, proposed: Flutter static hosting + TLS reverse proxy + PHP-FPM Laravel + PostgreSQL, with managed backup and monitoring. B adds concurrency and operational options but costs more and requires migration, deployment and support skills. B has not been built or benchmarked here. Keep A for the demonstration; evaluate B before actual enrollment commitments. See source docs/Architecture_and_Decision.md for the criterion comparison.

## Historical performance and UAT evidence
Week 16 records eight short same-host workload runs with 320 application requests and zero unexpected errors. Ordinary/peak profiles use 2/8 closed-loop virtual users; login is excluded from the measured loop. Latency gates pass6/8, throughput 5/8 and readiness-latency 0/8. Sampled PHP CPU was about 83-90 percent of one core and peak sampled working set about 47.94 MiB. These are historical measurements with a single PHP development-server worker, not new Week 18 benchmarks or real deployment capacity. The historical stopped-checkpoint/config rollback recovered in 1.179 seconds; it was not schema rollback or continuous production failover.

Week 17 acceptance criteria and developer observations are retained as prior evidence. No actual student/staff participant feedback or acknowledgement has been supplied. Stakeholder UAT remains PENDING; the final demonstration is not a replacement for that acceptance decision. No satisfaction percentage, signature or panel score is created.

## Limitations and final recommendation
Only fictional records and local controlled environments are verified. Real stakeholder UAT, representative deployment/load/soak evaluation, current dependency-audit refresh, accessibility review, production support ownership and institutional authorization remain outstanding. File-content validation/upload, automatic reminders, public registration/reset and live registrar integration are outside this implemented slice. No extra feature or production completion is implied by the final-week label.

Recommend GO for a supervised fictional technical demonstration and portfolio review, conditional on the recorded preflight checks. NO-GO for real enrollment/public peak commitments until those gaps are resolved. The actual defense, individual viva and E3 are to be performed by the student; this package prepares them but cannot record their outcomes in advance.

# Recorded Week 18 Verification
Release w18-final | Final developer rehearsal captured 2026-11-23T15:20:30.977Z
Project 23 | Sam Russell M. So, BSIT-3A

| Check | Actual result | Primary evidence |
|---|---|---|
| Laravel/PHPUnit | 32 tests, 120 assertions, zero failures | backend-tests.txt; backend-junit.xml |
| Flutter tests | 22 passed | flutter-tests.txt |
| Flutter analysis | No issues found | flutter-analyze.txt |
| Web build | Debug build, bundled resources, successful | flutter-build.txt |
| Verified HTTPS API regression | 47 requests, 147 assertions, zero failures | postman-results.json; postman-junit.xml |
| Final Chrome rehearsal | 9 developer checks, 13 real screenshots, zero JS errors | final-demo.json; final-demo-run.txt |
| API contract | 19 implemented operations, local references resolve | api-contract-check.json |
| Failure/recovery | Live 200/ready 503 during failure, ready 200 after restoration, business result retained | failure-recovery-demo.json |
| Current dependency audit | Unavailable - registry DNS lookup failed; no clean current result claimed | dependency-audit-attempt.txt |
| Stakeholder / panel / E3 | Genuine stakeholder acceptance pending; panel/viva/E3 outcomes not recorded | Prior_Evidence/Week17_UAT_Status; preparation guides |

## What the fresh recovery timing means
The observed configuration-restoration interval was 3046.2 milliseconds, measured from the start of restoring the helper configuration until an observed ready response. It includes helper stop/start and UI/probe delays; it is not an isolated server startup benchmark or a production RTO guarantee. The student UI then confirmed 1/3 approved after restoration. See JSON timestamps/status bodies. This is separate from the historical Week 16 checkpoint/config rollback of 1.179 seconds.

