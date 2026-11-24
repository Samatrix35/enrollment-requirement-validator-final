# Week 16 Performance and Reliability Results

Project 23 Enrollment Requirement Validator. Sam Russell M. So, BSIT-3A. 18 November 2026.

Eight planned runs completed with 320 measured application requests and 0 unexpected errors. Ordinary-use latency and throughput met their goals in all four ordinary runs. Peak latency met its goal in two of four runs; peak throughput met its goal in only one of four runs. Readiness stayed HTTP 200 during load, but its p95 latency exceeded the 1000 ms goal in every run. A successful HTTP response alone therefore did not satisfy operational readiness.

## Per-run measured results

| Environment | Profile/repeat | Requests | Requests/s | Client p95 ms | Health p95 ms | CPU % one core | Peak RSS MiB |
|---|---|---|---|---|---|---|---|
| development | ordinary 1 | 33 | 1.44 | 1535.9 | 1650.0 | 86.8 | 46.9 |
| development | peak 1 | 41 | 1.69 | 5281.7 | 5023.7 | 88.1 | 47.9 |
| development | ordinary 2 | 36 | 1.70 | 1368.0 | 1453.4 | 89.5 | 47.3 |
| development | peak 2 | 44 | 1.77 | 4891.3 | 4581.5 | 87.3 | 46.9 |
| controlled | ordinary 1 | 33 | 1.55 | 1412.8 | 1339.2 | 86.0 | 46.8 |
| controlled | peak 1 | 43 | 1.79 | 5023.7 | 5217.9 | 87.3 | 46.9 |
| controlled | ordinary 2 | 41 | 1.80 | 1157.7 | 1253.5 | 83.0 | 46.9 |
| controlled | peak 2 | 49 | 2.04 | 4326.8 | 4154.6 | 86.2 | 47.0 |

The complete CSV also includes p50/p99/maximum, actual elapsed seconds, error rate, sampled private memory and gate outcomes. Raw per-request records and health samples support recalculation. No failed performance run was replaced by a more favorable run.

## CPU memory and interpretation

The sampled backend CPU means ranged from 83.0 to 89.5% of one core; the largest sampled working set was 47.9 MiB. All eight runs have at least 21 process samples. High single-worker CPU usage, limited throughput growth and longer peak latency are consistent with pressure on the single PHP worker. This is an interpretation of the observed configuration, not proof that every deployment would have the same bottleneck. Host-wide CPU and total application-stack memory were not measured by the backend figures.

The controlled cached/TLS configuration was not consistently within peak targets. Two short repetitions on one host are insufficient to assert a statistically significant speedup or public capacity. Windows process overhead, the shared generator/collector, absent OPcache and the built-in server all limit extrapolation. A production worker pool and representative hosting need separate sustained tests before enrollment use.

## Operational signals and application changes

The public liveness endpoint returns a minimal alive/release signal. Readiness performs a database query and checks required student, account, requirement and token columns. A missing database or schema returns 503 without paths or exception details. The staff UI displays readiness and a safe failure message. Request IDs are validated and echoed; logs contain method, route template, status, duration and PHP peak heap bytes, never bearer tokens, passwords, request bodies or query values. PHP peak heap in logs is different from Windows sampled RSS.

A SQLite query-plan comparison on the isolated fixture changed from SCAN requirements to SEARCH requirements USING INDEX requirements_status_id_index for the submitted-review queue. This establishes index selection, not a separately measured whole-system latency improvement. WAL/busy timeout are configured, but a single API worker does not prove behavior with simultaneous multi-process database writers.

## Correctness and security regression

The completed checks passed: 30 Laravel tests with 106 assertions, 21 Flutter tests, static analysis, debug web build, and 46 Postman/Newman HTTPS requests with 144 assertions. These include the inherited enrollment and security flows plus readiness, minimal failure responses, trace IDs and the staff health dialog. The dependency audit reported no advisories at capture time. This does not certify an old installed PHP runtime or future dependency security.
