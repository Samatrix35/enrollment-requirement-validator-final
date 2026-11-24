# Data model and state contract
Project 23 | Week 17 | Laravel migration files are authoritative.

| Table | Purpose and constraints |
|---|---|
| students | Unique student_number; name, course, year_level and section; API accepts optional email but the StudentResource intentionally omits it. |
| requirements | Foreign key student_id cascades on student deletion; unique student/name pair; status enum Pending/Submitted/Approved/Rejected; nullable remarks. Composite status/id index supports queue filtering. |
| users | Unique email, hashed password, role and optional unique student_id. Student deletion nulls the link rather than deleting the account. A student account without a linked record receives404 for its checklist. |
| personal_access_tokens | Sanctum token hashes, owner relation, expiry and abilities. Plain tokens only appear on login and must not be persisted in screenshots or source. |
| framework tables | Laravel sessions, cache and jobs exist, but no reminder-job execution is claimed by this bounded application. |

## Relationships
One student has many requirements. A student may have one linked user account; staff users need no student link. Users have Sanctum access tokens. RequirementResource returns reduced fields to students and a staff-only student summary/remarks where loaded. Database backup and exports must be treated as sensitive outside the fictional fixture.

## Allowed transitions
Pending -> Submitted (own student only).
Rejected -> Submitted (own student only, clears old remarks).
Submitted -> Rejected (staff only, nonblank correction note <=500characters).
Submitted -> Approved (staff only).
Approved cannot be student-resubmitted, and a repeated staff decision returns409. Conditional updates enforce the current database status instead of trusting the browser. Staff may rename records/adjust remarks through the API; the Flutter slice exposes review decisions only. Empty requirement updates return422. Individual requirement approval does not grant institutional enrollment clearance.

## Migration caution
Run migrations on the disposable demo DB after setup. Review and back up any non-demo database before migration. The historical Week16 config/checkpoint drill does not prove safe rollback of arbitrary schema changes.
