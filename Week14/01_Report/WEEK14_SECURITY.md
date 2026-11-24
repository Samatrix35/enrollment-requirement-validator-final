# Week 14 secure integration

The Enrollment Requirement Validator separates student submission from staff decisions. Sanctum bearer authentication protects enrollment records. Middleware enforces student and enrollment_staff roles, while student submission checks the linked student ID before reading or changing a requirement.

| Control | Implementation | Evidence |
|---|---|---|
| Authentication | Hashed passwords, generic invalid credentials, Sanctum 60 minute tokens | Laravel feature tests and live API results |
| Logout | Deletes current personal access token | Revoked token returns 401 |
| Roles | Server middleware protects staff CRUD and student routes | Student staff-route request returns 403 |
| Ownership | Linked student ID must match requirement student ID | Cross-student submission returns 404 |
| Workflow | Pending or Rejected to Submitted; Submitted to Approved or Rejected | Live checks include 409 conflicts and correction cycle |
| Correction notes | Required for rejection, at most 500 characters | Missing note returns 422 |
| Validation | Laravel field rules and prohibited status on creation | Invalid creation returns 422 |
| Data minimization | Resources omit student email and account secrets | Checklist response and feature assertions |
| Response safety | no-store, nosniff, DENY, no-referrer headers | Feature assertions and saved HTTP headers |
| Transport | Production HTTPS middleware and release client HTTPS guard | Source inspection; public TLS deployment not performed |
| Login throttling | Five requests per minute | Route configuration; not separately load tested |
| Logging | Event names and IDs; no password or bearer token fields | Sanitized security event excerpt |

## Trust boundaries

Flutter browser → authenticated Laravel API → SQLite. Browser UI controls are convenience only; the API independently checks roles and ownership. CORS allows the configured local frontend origin. Demo data represents fictional students and does not integrate with university records.

## Remaining risks

This is a Week 14 prototype. It has no scanned file upload validation, reminder scheduler, backup procedure, production monitoring, password recovery, or external identity provider. In-memory client token storage reduces persistence but does not prevent XSS or compromised-browser attacks. A production rollout needs a reviewed content security policy, secure hosting, backups, concurrent workflow review, and broader security testing. SQLite and the local PHP development server are for demonstration.
