# Week 15 Test Plan and Design

Project 23 Enrollment Requirement Validator. Sam Russell M. So, BSIT-3A. IA6, 18 November 2026.

The verification boundary is the student checklist, staff review queue and authenticated Laravel API. The normal path is Pending → Submitted → Approved. The correction path is Submitted → Rejected with a note → Submitted → Approved. Enrollment progress counts approved items only. The prototype records checklist state and does not upload document files.

The primary users are students and enrollment staff. Advisers and authorized reviewers are contextual users in the guide; dedicated adviser accounts and screens are not part of this bounded slice. Fictional records and local-only demo credentials are used throughout.

## Test strategy

Laravel feature tests exercise database and server middleware rules, authentication, ownership, workflow, input lengths, data minimization, logging, throttling, pagination, and production transport rules. Flutter API tests cover response contracts, timeout, error messages, tokens and pagination. Flutter screen tests cover input validation, error/retry states, submission, resubmission, staff decisions and expiry. Newman verifies the real API over HTTPS. Chrome verifies the compiled Flutter–Laravel workflow, offline recovery, revoked sessions and responsive layouts.

The detailed matrix contains 40 named test mappings in REQUIREMENTS_TRACEABILITY.csv. It identifies the expected behavior, test reference, observed result and raw evidence. Tests are executable rather than screenshots of invented results. Test doubles and local fixture manipulations are identified explicitly in VERIFICATION_NOTES.md.

## Entry and exit criteria

Entry requires the provided Week 14 enrollment source, isolated SQLite test data, reproducible dependency lockfiles and runnable PHP/Flutter environments. Exit requires all 23 backend tests, all 19 Flutter tests, the 43-request/130-assertion Postman run, static analysis and browser scenarios to pass; every reproduced priority defect must link to a fix and a regression result. Known limits must remain visible rather than being presented as completed features.

## Categories and evidence

- Functional: complete submission, correction, resubmission and approval path; persistent student result.
- Invalid input and boundary: requirement name 120/121, correction note 500/501, first name 80/81, blank notes and missing records.
- Interoperability: JSON, bearer headers, API pagination and compiled Flutter to Laravel interaction.
- Authentication and authorization: invalid/altered/expired/revoked tokens, role denials and cross-student requests.
- Reliability and recovery: safe service failure, stalled body timeout, explicit retry and session reset.
- Security and hardening: hashing inherited from Laravel, no diagnostic leakage on 5xx, minimized responses, defensive headers, safe logs, duplicate constraint, dependency audit and verified local HTTPS.

## Architecture and decisions

Flutter browser → JSON API → Laravel authentication, validation and role/ownership checks → SQLite. Local HTTPS tests add a loopback TLS proxy before Laravel. Application configuration trusts only the explicitly configured proxy. The browser holds the token in memory. Failed writes are not retried automatically; users choose a retry after inspecting the current state. Conditional database updates check the prior workflow state atomically. The queue follows at most 100 pages; exceeding the cap produces a visible error rather than silently dropping records.
