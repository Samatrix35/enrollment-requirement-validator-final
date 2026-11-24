# Week 15 Findings Hardening and Submission

Project 23 Enrollment Requirement Validator. Sam Russell M. So, BSIT-3A. IA6, 18 November 2026.

## Hardening implemented

Duplicate assignments are rejected by validation and a database unique constraint. Staff decisions and student submissions use conditional database updates so a stale workflow state cannot be silently overwritten by those operations. Authentication expiry runs before response parsing; refresh futures are handled safely when the screen is removed. Server diagnostics are replaced with a safe 5xx message. Successful response shape is validated. Review queue pagination is bounded and explicit. A whole-response timeout covers both headers and streamed body. Login validation is scrollable at small viewport heights. Compatible Composer dependencies were updated and audited again.

## Residual risks and limitations

| Risk | Current boundary | Follow-up |
|---|---|---|
| Public deployment | Local servers and test-only TLS | Public trusted TLS, secret storage, reviewed proxies and monitoring |
| SQLite concurrency | Conditional updates and a unique index; no parallel stress run | Test realistic multi-process contention and database choice in Week 16 |
| Browser compromise | Memory token, safe display and minimized payloads | Content security policy, browser security review and stronger identity controls |
| Queue size | Explicit cap of 100 pages (50 records each) | Add paged UI and operational limits for larger workloads |
| Duplicate meaning | Exact database uniqueness only | Consider normalized or semantic duplicates after a business decision |
| Document handling | Checklist state only | File upload/type/content checks require a separate design |
| Automation scope | No reminder scheduler or retry worker | Week 13 automation remains outside this inherited slice; do not claim it exists |
| Account lifecycle | Demo sign-in only | Registration, reset, MFA and recovery need separate work |
| Dependency audit | No advisories at the recorded audit time | Repeat audit and patch review before deployment |

## Maintenance and recovery

Use the lockfiles, run tests before changes, and preserve an authorized backup before schema migration. The unique index deliberately fails on pre-existing duplicate pairs. Review and resolve duplicates rather than dropping records silently. Browser reload clears the in-memory session. A service failure offers an explicit retry. Failed write requests are not automatically repeated. Application logs contain IDs and event names; do not extend them to credentials, tokens or raw student documents.

## Submission and demonstration

Review the README, registration documents, three reports, traceability matrix, defect register, evidence gallery, raw logs, source and Postman collection. Run the setup on a writable local folder and use the VS Code tasks. Demonstrate student submission, staff correction, student resubmission, staff approval, persistent progress, a safe outage/recovery, and revoked-session handling. Use the saved evidence as a fallback if a local demo machine is unavailable.

The registration form and approval letter are prepared for review. Instructor approval, signature and date remain blank. No institution-wide registry or classmate data is included. The project and evidence were prepared with AI assistance; the student should review, reproduce and be able to defend the implementation and tests.
