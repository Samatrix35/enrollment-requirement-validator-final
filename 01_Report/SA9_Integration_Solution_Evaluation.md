# SA9 - Integration Solution Evaluation
Project 23 | Enrollment Requirement Validator | Week 17
Sam Russell M. So | BSIT-3A | 23 November 2026

## Executive decision
The bounded student/staff workflow is implemented and developer verification is recorded. Stakeholder acceptance remains PENDING: no actual student/staff session, feedback or acknowledgement has been supplied. Recommend supervised fictional demonstration only. Do not claim production readiness or completed stakeholder UAT.

## Scope and evidence method
Continues the user's Week 16 Project 23 archive. The folder format is reused, not another project's subject matter. Fresh Week 17 regression, API and browser runs are identified in 03_Logs. Original Week 16 measurements are isolated in Week16_Baseline and do not imply Week 17 performance was remeasured. Screenshots support source/tests; they do not replace reproducible evidence.

## Acceptance evaluation
The UAT plan defines ten scenario criteria and decision authority. AC01-04 cover login, ownership, submission, correction/resubmission and approval. AC05-07 address invalid inputs, authorization and actionable correction validation. AC08-10 cover responsive presentation, readiness/recovery and logout. Developer rehearsals verify behavior; stakeholder usability, institutional workflow fit and acceptance are still open. See Acceptance_Matrix.csv for precise evidence and status.

## Findings and disposition
D01: Empty correction note previously did nothing when Send note was clicked. The dialog now displays Enter a correction note and preserves the draft; no PATCH occurs until valid. Reproduced with a failing widget test, fixed and rerun. Severity medium, developer-closed, stakeholder retest pending.
D02: Empty staff PATCH lacked a defined validation guard. It now returns HTTP 422 with an update validation error instead of an empty database update. State remains Submitted. API regression captures the before/fix/retest. Severity medium, developer-closed.
L01: Genuine stakeholder UAT/approval absent. Owner: student facilitator plus actual participant/instructor; remains open.
L02: Week 16 peak throughput/health-latency gates missed. Owner: deployment maintainer; remains open; no performance fix is claimed this week.
L03: Reminder automation, file-upload verification, external registrar integration, password reset and multi-instance deployment are outside the current bounded slice. They must not be described as completed features.

## Quality evaluation
- Interoperability: Flutter uses JSON API over HTTP locally; verified HTTPS API regression checks protected exchange. OpenAPI describes all application API routes, role requirements and expected error behavior.
- Security: Sanctum token expiry/revocation, server-side roles, ownership, HTTPS enforcement in production, limited CORS and safe messages are covered by inherited and new tests. No independent penetration test or security certification is claimed.
- Reliability: database/schema readiness, safe unavailable state, recoverable UI and the historical rollback drill provide bounded evidence. They are not a long-term availability measurement.
- Performance: inherited Week 16 eight-run/320-request baseline had zero unexpected errors; p95 gates passed 6/8, throughput 5/8, health response 0/8. Configuration rollback recovered in 1.179 seconds. Same-host single-worker PHP tests cannot establish real enrollment capacity.
- Usability: developer browser rehearsal records main role flows and a narrow viewport. D01 improves visible validation. No stakeholder satisfaction score, WCAG certification or human usability success rate is invented.
- Maintainability: locked dependencies, feature/widget/API tests, OpenAPI, change log, role-based operating tasks and known limitations are supplied. Dependencies must be installed before use; vendor/build/runtime data are excluded from submission.

## Recommendation and next decision
Proceed with a supervised fictional classroom demonstration. Conduct actual UAT using the supplied participant form, obtain genuine acceptance/disposition, then address capacity and operational gaps in a representative environment before any real enrollment use. Week 17 portfolio preparation is complete; stakeholder acceptance is not.

## Additional verified Week 17 improvement
D03 removes the external CanvasKit CDN dependency from the local build command. Initial fetch failure and final successful browser rehearsal are retained. Current dependency audit could not refresh because registry DNS/network was unavailable; historical Week16 audit is not treated as current. See Verification_Summary.md for the complete fresh test counts.
