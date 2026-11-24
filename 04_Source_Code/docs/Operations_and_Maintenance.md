# Operations and maintenance
## Ownership
Student developer maintains the code; enrollment staff decide checklist outcomes; a designated operator owns deployment/backups; institutional authority approves real data/use. These roles do not imply named people have agreed to support the application.

## Daily/session checklist
Check /api/health/live (200 only proves process liveness) and /api/health/ready (200 requires DB/schema). Record release and request ID; investigate 503 without exposing paths or secrets. Review error rates, response time and DB/file capacity. Week 16 health p95 targets failed, so do not treat occasional green status as an availability SLA. Do not log passwords, access tokens, request bodies or student records. Restrict and rotate logs; choose retention with institution policy before real use.

## Backup/restore and incident response
For this isolated SQLite demo stop writers, run WAL checkpoint/truncate and copy the DB with release/config metadata to a private location; do not back up only the main file while uncheckpointed WAL contains writes. Prove restore into a separate environment, check schema/readiness, authenticated checklist and selected records. Treat DB backups as sensitive, never include them in coursework ZIPs. During an incident stop changes, record trace IDs/status, restore the known-good release/config and compatible stopped checkpoint, verify readiness/business data and document lost writes. Week 16 tested only controlled config rollback, not destructive schema rollback or continuous RPO0. Do not run rollback scripts against a real DB.

## Recurring maintenance
Before changes: create branch/backup, record requirement and acceptance criterion, review API compatibility. For each release: Composer audit, dependency review, backend/Flutter/API tests, analyze/build, controlled rollout and rollback verification. Monthly or when advisories appear: update dependencies in an isolated branch, rerun regression and inspect security configuration. Review tokens, CORS allowlist, TLS certificate validity and least-privilege DB/filesystem access. Never set trusted proxies broadly without a controlled proxy boundary. Use APP_DEBUG=false and private secrets in any approved deployment.

## Support and escalation
Student workflow issues -> enrollment staff role. 401 -> sign in again. 403 -> role review; never bypass. 409 -> refresh state. 422 -> correct input. 503/network -> operator checks dependencies/config then performs controlled recovery. Security/privacy issue -> institutional authority/operator; preserve minimal evidence and stop use of affected records. No real support contact or service commitment has been invented.

## Open work
Real stakeholder UAT, representative peak/soak/load tests, human accessibility review, approved production design, backup retention/restore policy and real support assignment remain pending. Reminder automation, file upload checking and public account lifecycle are out of scope.
