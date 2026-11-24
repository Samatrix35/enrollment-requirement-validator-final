# Final Demonstration and Recovery Guide
Project 23 | Week 18 | Sam Russell M. So, BSIT-3A
Prepared presentation script - not a record of a completed panel defense.

## Preflight and test data
Use one isolated fictional demo database and the two supplied local demo roles. Install locked dependencies with Setup-Demo.ps1. Build with Build-Local-Web.ps1, which bundles CanvasKit instead of relying on the external CDN. Start the API on 127.0.0.1:8046 and built web on 127.0.0.1:5046. Confirm live 200 and ready 200, release w18-final, working browser, correct origin and the fresh verification logs. Close conflicting helper servers before starting the automated rehearsal. Do not reset a database containing needed records.

Demo student is Jordan Example, not the author: student.demo@example.test / StudentDemo-2026!. Demo staff: staff.demo@example.test / StaffDemo-2026!. These are known fictional local credentials, never production credentials. The automated runner explicitly requires --reset-demo and resets only the configured disposable demo DB; check the path before using it.

## Suggested 10-12 minute live narrative
1. Opening (1 minute): identify Project 23, users, boundary and problem. State that the validator records staff decisions rather than issuing institutional enrollment clearance.
2. Validation/login (1 minute): show required-field feedback, sign in as student and identify the own checklist. Explain that backend checks remain authoritative even when the UI validates.
3. Submission (1 minute): submit Birth Certificate. Show Submitted and0/3 approved; explain why submitting does not mean approved.
4. Staff correction (2 minutes): sign out, sign in as staff and show the queue. Try an empty correction note, observe useful validation, then send a brief correction. Explain role middleware and the Submitted-only transition.
5. Student resubmission (1 minute): sign in as student, read the exact correction note and resubmit. Show that the obsolete note disappears.
6. Staff approval and final result (1 minute): staff approves; the item leaves the queue. After the recovery segment, return as student and show 1/3 approved. Two items remain Pending.
7. Security/API proof (1 minute): open the fresh Postman/Newman result and source middleware/controller. Point to wrong-role 403, cross-student404, invalid 422, state 409 and revoked-token 401 checks. Do not expose an actual token on screen. Newman is the scripted Postman-compatible runner, not evidence of using the Postman GUI manually.
8. Controlled failure/recovery (2 minutes): follow the safe sequence below. Show that liveness and readiness differ, recovery restores service and the approved business record remains.
9. Closing (1 minute): identify the evidence map, historical performance gaps, pending stakeholder UAT, proposed architecture alternative and conditional demonstration-only readiness.

## Controlled failure-and-recovery sequence
Run `node tools/run-week18-demo.cjs --reset-demo` only with the fictional local data after building web; it owns its helper servers. It starts Laravel with the normal configuration, checks readiness, then restarts only that helper with DB_DATABASE pointing to a deliberately missing local file. It confirms liveness 200 but readiness 503 and captures the safe staff message. It restores the valid configuration, confirms ready 200 and later checks that the student still sees1/3 approved. The failure-recovery-demo.json records actual statuses/timestamps and the observed configuration-restoration interval. This is a configuration/dependency demonstration, not a destructive DB/schema rollback.

For a manual presentation, use the same bounded steps in separate local terminals: stop the demo API, start a temporary helper process with an invalid DB_DATABASE, inspect health, then close that helper terminal and restart using the unchanged valid local .env. Never edit or delete the valid database to simulate failure. Confirm both readiness and authenticated business results after restoration. A service returning200 at /live alone is not recovered business functionality.

## Backup evidence and failed-demo contingency
Open02_Evidence/index.html locally; all 13 application screenshots are saved at full resolution and captions point to source/raw logs. If the live demo fails, say what failed, show the matching recorded outcome and its timestamp, and distinguish recorded evidence from a live run. Use fresh final-demo.json, failure-recovery-demo.json, postman-results.json and the code rather than improvising success. If no internet is available, the bundled local build and evidence gallery remain usable; dependency installation and advisory refresh may still need connectivity beforehand.

## Historical restore proof
Prior_Evidence/Week16_Performance/rollback-recovery.json records the earlier stopped SQLite checkpoint/config restore with matching checkpoint/restored hashes and1.179second recovery. Use it only as dated backup evidence. It does not prove that new writes can never be lost, that migrations are reversible, or that a public multi-host deployment will meet the same timing.

## End-of-demo checklist
Confirm the final business state, sign out and stop only the helper servers you started. Preserve evidence logs and do not include private .env, token values, database backups or TLS private keys in the submission. Record any actual panel question/outcome afterward; no panel result is prefilled here.


## Recorded final rehearsal result
The fresh developer browser run completed 9 checks and13 actual screenshots with zero JavaScript errors. During the deliberate configuration failure, liveness returned 200 and readiness 503; after valid configuration restoration readiness returned 200 with release w18-final. The observed configuration restoration-to-ready interval was 3046.2ms (including helper/UI/probe delays), then the student checklist still showed 1/3 approved. This is a recorded local rehearsal, not an actual panel defense or production recovery SLA. JSON evidence:03_Logs/failure-recovery-demo.json and final-demo.json.
