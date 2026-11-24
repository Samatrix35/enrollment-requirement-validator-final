# E3, Project Defense and Individual Viva Preparation
Project 23 | Enrollment Requirement Validator | Week 18
Prepared for Sam Russell M. So, BSIT-3A
Practice material based on this implementation, not the actual examination paper or a completed viva.

## Opening explanation
Suggested wording to rehearse: "My Project 23 is an enrollment requirement checklist and staff-review system. Flutter Web presents the student and staff workflows, Laravel enforces validation and authorization, and SQLite stores fictional records. A student can submit or resubmit their own item, but only staff can approve a submitted requirement. My evidence includes tests, actual browser captures, API results and dated performance findings. The recommendation is supervised demonstration only because stakeholder acceptance and real deployment readiness are still outstanding."
Use your own words and be ready to open the implementation. A memorized description alone cannot demonstrate understanding.

## Questions to defend with source and evidence
### 1. Trace a single Submit click end to end.
StudentPage._submit calls ApiClient.submitRequirement, which sends PATCH /my/requirements/{id}/submit with the in-memory bearer token. Sanctum/role middleware authenticates and limits the role. MyEnrollmentController.submit checks the linked student and ownership, verifies Pending/Rejected and performs a conditional update to Submitted while clearing remarks. The response refreshes the checklist. Find the matching request/status and source in Final_Evidence_Map.csv.

### 2. Why is hiding an Approve button insufficient?
An attacker can call the API directly. The server's role middleware protects staff routes even if the Flutter client is replaced. The ownership check also prevents one student from submitting another student's item. Demonstrate with the403/404 API tests, not with a screenshot of a missing button.

### 3. Authentication, authorization, CORS and TLS: what differs?
Authentication establishes identity through a valid token. Authorization checks allowed role/resource actions on the server. CORS is a browser-origin policy and does not stop a non-browser client from making requests. TLS protects transport; it does not grant a student staff privileges. Production HTTP is rejected with 400; controlled API tests trust the generated local CA certificate and do not disable TLS verification.

### 4. What happens when two staff decisions race?
Only Submitted items may receive a decision. The update query includes the current Submitted status, so a stale second update cannot overwrite the first completed decision; it gets409. This is a bounded state-conflict control, not proof of unlimited concurrent capacity or a full edit-history feature.

### 5. Why are there422 and409 responses?
422 describes invalid data, such as a missing rejection note or empty requirement update.409 describes a valid-looking operation that conflicts with the current state, such as a repeated approval. The client should correct invalid input for 422 and refresh/review current state for 409 rather than blindly repeat a decision.

### 6. Why separate liveness and readiness?
The PHP/Laravel process can answer while its DB dependency is unavailable. /health/live then returns 200, but /health/ready returns 503. Readiness checks a real DB query and required schema. After configuration restoration, verify readiness and an authenticated checklist; an HTTP 200 alone cannot establish correctness of every function.

### 7. How are secrets and student data handled?
The example config contains no live APP_KEY. Private .env, databases, TLS private keys and installed/build caches are excluded. Passwords are hashed, Sanctum stores token hashes, and tokens remain in client memory rather than public source/browser storage. Logs record safe route/timing/status metadata, not request bodies or token values. The fixed demo credentials are fictional local training values, never production secrets.

### 8. Does logout always revoke the server token?
A successful online logout deletes the current Sanctum token and is verified by a subsequent401 request. If the network is unavailable, the client still clears local state, but cannot prove server revocation. Explain that residual risk and expiry/operator controls rather than claiming all offline tokens were revoked.

### 9. Why choose this architecture over the alternative?
The implemented Flutter/Laravel/SQLite monolith matches a small supervised fictional demonstration, existing skills and low setup cost. PHP-FPM/PostgreSQL behind a controlled TLS proxy offers different concurrency/operations options but adds cost, migrations and support duties. It is a proposal, not a tested claim of better throughput. Reconsider when real public usage, write concurrency and recovery obligations become requirements.

### 10. Explain the performance findings without overstating them.
Historical Week 16 tested2/8 closed-loop virtual users on the same Windows host and one PHP development-server worker, with short repetitions and preissued tokens.320 application requests had no unexpected errors, but latency passed 6/8 runs, throughput 5/8 and readiness latency 0/8. CPU percentages are one-core equivalents; RSS is sampled working set. These observations do not establish an availability SLA, login-storm capacity or production peak readiness. Fresh Week 18 regression is a different kind of evidence and must not be relabeled as a new load test.

### 11. What is UAT, and what is its actual status?
UAT asks actual stakeholders whether the bounded workflow meets their acceptance needs. Developer tests and this demonstration verify technical behavior but cannot supply stakeholder feedback. Week 17 criteria and developer results exist; genuine participant feedback/acceptance remains pending. No sign-off or satisfaction score is assumed.

### 12. What is complete and what remains?
The implemented checklist/review slice, controls, technical verification and final portfolio are prepared. File-content validation/uploads, automated reminders, public account lifecycle and live registrar integration are outside this slice. Actual stakeholder acceptance, representative deployment checks, advisory refresh, support ownership and production authorization remain open.

## E3 analytical practice
### Case A: API returns 200 but students cannot complete tasks.
Separate transport status from business success. Inspect the actual response schema, authentication/role, state transition and UI error/loading state. Check request ID and safe logs, reproduce with a fictional item and add a focused regression if a new defect is found. Do not conclude that200 or a health-green screen proves workflow success.

### Case B: Peak traffic has no errors but long queues.
Read throughput and p95 latency together and distinguish application requests from health probes. A single PHP worker can delay readiness probes behind business work. Gather process/DB/queue evidence before assigning a cause. Compare a representative PHP-FPM/PostgreSQL environment with fixed workloads before recommending capacity. Do not invent a causal speedup or assume an index alone resolves all latency.

### Case C: A proposed migration breaks readiness.
Stop further changes, preserve minimal evidence and identify whether the failure is config, connectivity or schema compatibility. Restore a known-good compatible release/config only after checking data implications. A destructive migration may require a planned data restore/forward repair; the historical stopped-checkpoint drill does not establish arbitrary schema rollback or zero lost writes.

### Case D: A stakeholder requests automatic enrollment clearance.
Clarify that the current system records staff-approved checklist items. Institutional clearance changes scope, rules, authority, audit needs and acceptance criteria. Document the change request and required evidence/authorization before changing the business meaning of Approved. Do not turn a UI count into an official decision without validated requirements.

## Individual viva rehearsal and panel review
Practice locating the source symbols above, explaining one adverse case, reading one real test assertion and discussing one limitation without assistance. Identify coding-assistant support honestly; do not claim independent execution, interviews or approvals that were not observed. Practice questions are not predicted exam questions; follow the instructor's actual exam conditions.

Actual defense date: ____________________
Panel question / source shown / student's answer: ____________________
Follow-up finding and owner: ____________________
Panel/individual viva outcome: NOT YET RECORDED
E3 examination outcome: NOT YET RECORDED
