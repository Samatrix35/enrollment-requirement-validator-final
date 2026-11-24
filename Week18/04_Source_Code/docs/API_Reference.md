# Implemented API reference
Project 23 | Week 18 | Release w18-final

Contract: openapi.json (OpenAPI 3.0.3). Run `node tools/check-api-contract.cjs` to compare route coverage and resolve local references. This is not independent full-schema certification.

Local base: http://127.0.0.1:8046/api. Production requires HTTPS (400 otherwise). Protected requests use Accept: application/json and Authorization: Bearer <temporary-token>. Never publish actual token values.

| Method | Path | Role | Success | Expected errors |
|---|---|---|---|---|
| GET | /health/live | Public | 200 | 400 |
| GET | /health/ready | Public | 200 | 400,503 |
| POST | /auth/login | Public | 200 | 400,422,429 |
| GET | /me | authenticated | 200 | 400,401 |
| POST | /auth/logout | authenticated | 200 | 400,401 |
| GET | /my/enrollment | student | 200 | 400,401,403,404 |
| PATCH | /my/requirements/{requirement}/submit | student | 200 | 400,401,403,404,409 |
| GET | /students | enrollment_staff | 200 | 400,401,403 |
| POST | /students | enrollment_staff | 201 | 400,401,403,422 |
| GET | /students/{student} | enrollment_staff | 200 | 400,401,403,404 |
| PUT | /students/{student} | enrollment_staff | 200 | 400,401,403,404,422 |
| PATCH | /students/{student} | enrollment_staff | 200 | 400,401,403,404,422 |
| DELETE | /students/{student} | enrollment_staff | 200 | 400,401,403,404 |
| GET | /requirements | enrollment_staff | 200 | 400,401,403,422 |
| POST | /requirements | enrollment_staff | 201 | 400,401,403,422 |
| GET | /requirements/{requirement} | enrollment_staff | 200 | 400,401,403,404 |
| PUT | /requirements/{requirement} | enrollment_staff | 200 | 400,401,403,404,422,409 |
| PATCH | /requirements/{requirement} | enrollment_staff | 200 | 400,401,403,404,422,409 |
| DELETE | /requirements/{requirement} | enrollment_staff | 200 | 400,401,403,404 |

## State and response rules
Login issues expiring Sanctum bearer tokens; invalid credentials return 422. Login is limited to 5 attempts/minute. Logout revokes the current token. Staff role is enrollment_staff, student role is student.
Lists are paginated at 50 records/page; optional status filter accepts only the four defined states. Staff Flutter queue aggregates pages with a100-page safety cap. It is not a live snapshot under concurrent changes.
Student resources omit staff-only remarks/student summaries. Rejected items expose a correction note as feedback; resubmission clears it. Require a nonblank rejection note <=500 characters. Empty requirement PATCH returns 422. Stale/non-submitted decisions return 409.
Readiness 503 returns safe status/checks only; live 200 alone cannot establish dependency health. API responses are no-store and carry trace/timing metadata where the request reaches that middleware. Laravel /up is framework liveness, outside this application API.
See Data_Model.md, User_Guide.md and Operations_and_Maintenance.md for relationships, workflow and support.
