# AS3 - Integrated Advanced Technical Solution Report
Project 23 | Enrollment Requirement Validator | Week 17
Sam Russell M. So | BSIT-3A | University of La Salette
Prepared 23 November 2026

## Problem, users and boundary
Students need a consistent view of assigned enrollment requirements and correction feedback; enrollment staff need a protected review queue. The implemented slice tracks Pending, Submitted, Rejected and Approved records using fictional data. It supports staff decisions, not actual institutional enrollment authorization. Scope excludes file-content validation, automatic reminders, public registration/reset and live registrar integration.

## Integrated architecture
Flutter Web holds the bearer token in application memory, submits JSON to Laravel/Sanctum and renders loading/error/success states. Laravel performs authoritative validation, ownership and role checks. Eloquent persists students, requirements, users and hashed access tokens in SQLite. Request tracing records route templates, timing and PHP heap metrics without body/token contents. Readiness checks database and schema separately from liveness. See Architecture_and_Decision.md and architecture.svg.

## Data and API behavior
A student has many requirements; a user may link to one student; staff users are not student owners. Unique student_number and per-student requirement-name constraints prevent duplicates. Pending/Rejected can be submitted by the owner. Only Submitted can receive a staff decision, and rejection requires a note. Conditional database updates guard stale status changes. Returning an item exposes student-safe feedback; resubmission clears old remarks. A full OpenAPI 3.0.3 JSON contract is included; example placeholders never contain live credentials.

## Alternatives and recommendation
Alternative A: Flutter Web + Laravel modular monolith + SQLite, one supervised demonstration host. Lower setup cost, transactional local data and clear ownership suit the current learning scope. Drawbacks include one-worker queueing, SQLite write contention and single-host failure.
Alternative B: Flutter static hosting + reverse proxy/TLS + PHP-FPM Laravel + PostgreSQL with managed backups and centralized monitoring. This separates processes and supports more realistic concurrency and operational management but adds deployment skills, cost, migrations, monitoring and backup ownership. It has not been deployed or benchmarked in this work.
Recommend A for fictional classroom demonstration only. Prefer evaluating B before real enrollment use, subject to approved infrastructure, authorized data and realistic capacity/security/UAT checks. A proposed design is not evidence of its performance. Detailed comparison scores/assumptions are in Architecture_and_Decision.md.

## Implementation and change control
Release w17-r1 preserves Week 15 access control and Week 16 health/trace features. D01 adds visible required-note validation in Flutter. D02 rejects empty staff updates with 422. Test-first repro logs preserve observed failures; fresh regression runs show the final result. Changes are bounded and reversible. Source lockfiles, .env.example, VS Code workspace/tasks and Postman collection are provided; no claim that a particular editor GUI was used is made.

## Verification and evaluation
The package contains Laravel feature/JUnit, Flutter widget/API tests and analyze/build logs, verified-TLS Newman assertions, browser acceptance rehearsal, source hashes and a traceability matrix. See Verification_Summary.md for exact fresh counts. API tests verify roles, ownership, state transitions, validation, expiry/logout and health. Browser captures show real compiled Flutter screens, not generated mockups. A screenshot cannot prove server authorization, so API tests are retained separately.

## UAT and evidence ethics
User acceptance requires actual stakeholder participation. No participant was impersonated, feedback invented or signature filled. A structured UAT script, acceptance criteria and blank decision record are supplied. Developer checks are explicitly labeled. Stakeholder workflow fit, accessibility observations and acceptance remain pending. Fictional Jordan Example is test data, not the project author.

## Performance and deployment limitations
Week 16 baseline: eight short same-host runs, 320 application requests, zero unexpected errors; ordinary/peak workloads were 2/8 closed-loop virtual users. Some peak latency/throughput and every health-latency gate failed. Rollback recovery was 1.179 seconds for stopped SQLite checkpoint/config restoration. This is historical evidence, not fresh Week 17 throughput or schema-failover proof. Conditional local-demo readiness remains the recommendation.

## Handover and maintenance
Setup installs locked dependencies, creates local .env and fictional database, then launches API8046 and Flutter5046. Use debug web for local HTTP only; release client requires HTTPS API. Operator checks readiness and request IDs, controls logs and tests backups. Maintainer reviews dependencies, runs regression, documents changes and verifies restoration. Support contacts are roles, not invented people. See Setup_and_Reproduction.md, User_Guide.md, Operations_and_Maintenance.md and API contract.

## Final disposition
Implementation, developer verification and handover portfolio are prepared. Actual stakeholder UAT, representative deployment evaluation and production authorization remain outstanding. Next work is to run genuine participant sessions, resolve their findings and validate a suitable deployment before real data or enrollment commitments.

## Additional verified Week 17 improvement
D03 removes the external CanvasKit CDN dependency from the local build command. Initial fetch failure and final successful browser rehearsal are retained. Current dependency audit could not refresh because registry DNS/network was unavailable; historical Week16 audit is not treated as current. See Verification_Summary.md for the complete fresh test counts.
