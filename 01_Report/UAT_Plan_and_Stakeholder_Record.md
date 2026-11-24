# UAT Plan and Stakeholder Record
Project 23 | Week 17 | Sam Russell M. So, BSIT-3A | 23 November 2026

## Purpose and decision authority
Confirm whether the bounded enrollment checklist workflow meets student/staff needs. Technical regression tests remain separate. Only an actual authorized student/staff representative or designated instructor can provide stakeholder observations and an acceptance decision. Developer rehearsal is not their approval.

## Environment and data
Use fictional Jordan Example, student.demo@example.test and staff.demo@example.test in an isolated local SQLite database. Setup creates disposable demo credentials; never reuse them for real users. Chrome connects Flutter Web at 127.0.0.1:5046 to Laravel at 127.0.0.1:8046. This HTTP browser rehearsal is local; the separate API regression uses verified local TLS. No public deployment is tested.

## Facilitation
Explain the task without prescribing the clicks. Obtain consent for recording feedback; use participant role/anonymous code, not personal records. Let the participant attempt the task unaided, note observed difficulties and distinguish defects from new feature requests. Record actual time, browser, task outcome and verbatim feedback only if supplied. Stop if private data appears. Retest accepted fixes with the participant where possible.

## Scenarios and expected outcomes
- AC01: Sign in with valid student credentials. Only that student's checklist appears.
- AC02: Submit a Pending requirement. It becomes Submitted and appears in staff queue; it is not auto-approved.
- AC03: Staff returns the item with a correction note. Student sees Correction needed and the exact note after refresh.
- AC04: Student resubmits; staff approves. Stale correction feedback disappears and approved count increases.
- AC05: Invalid login/blank fields give useful feedback without displaying protected records.
- AC06: Student cannot perform staff API actions or submit another student's item; 403/404 are expected.
- AC07: Staff tries an empty correction note. Visible required-note feedback appears, no PATCH is sent; a valid note works.
- AC08: At 390x844 and desktop width, principal student controls remain usable. Keyboard focus and labels must be observed by a human; screenshots alone do not establish full accessibility.
- AC09: A readiness failure gives a safe message; recovery restores a working status.
- AC10: Sign out revokes the token; subsequent protected requests require a new login.

## Acceptance thresholds
All critical workflow/access-control scenarios must succeed. No unresolved critical/high defect in the bounded workflow; remaining usability issues explicitly accepted by the stakeholder decision-maker. Technical gates do not imply stakeholder satisfaction, real enrollment correctness or peak capacity. Record conditional acceptance/rejection and follow-up owner/date rather than assuming sign-off.

## Actual stakeholder record - PENDING
Participant role/code: ____________________
Consent and session date/time: ____________________
Facilitator and environment: ____________________
Scenario ID / observed steps / actual outcome: ____________________
Participant's own feedback: ____________________
Defect or request ID / severity / owner: ____________________
Fix and participant retest result/date: ____________________
Decision (accept / conditional accept / reject) and reasons: ____________________
Decision authority acknowledgement/date: ____________________

No actual participant feedback or approval has been supplied. These fields are intentionally blank. Developer-run browser/API outcomes appear separately in the SA9 report and raw logs.
