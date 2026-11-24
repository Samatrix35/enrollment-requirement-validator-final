# Week 16 Workload and Measurement Plan

Project 23 Enrollment Requirement Validator. Sam Russell M. So, BSIT-3A. 18 November 2026.

The objective is to measure the enrollment prototype under bounded ordinary and peak workloads, verify operational signals, compare development with a controlled deployment configuration, and rehearse recovery from a failed change. The application supports student checklist submission, staff correction and verification. Real student records are not used.

## Workload and targets fixed before measurement

| Property | Ordinary | Peak |
|---|---|---|
| Concurrent virtual users | 2 | 8 |
| Think time after each operation | 250 ms | 0 ms |
| Active loop window | 20 seconds | 20 seconds |
| Repetitions per environment | 2 | 2 |
| Client p95 latency goal | at most 2000 ms | at most 5000 ms |
| Completed-request throughput goal | at least 1 request/s | at least 2 requests/s |
| Unexpected error goal | below 1% | below 1% |
| Readiness p95 goal | at most 1000 ms | at most 1000 ms |

The recovery target was 15 seconds from starting rollback to restored readiness. These are prototype test goals selected for this exercise, not institution-provided service-level agreements. All planned repetitions are retained; thresholds were not relaxed after seeing the results.

The fixture contains 200 fictional students, three requirements per student and an enrollment staff account. Each worker has its own student/requirement pair. The five-operation cycle contains three student checklist reads, one staff queue read, and one submission followed by a staff return with a fictional correction note. The last operation makes two HTTP requests. Repeated return/resubmit cycles exercise writes without changing a real student's eligibility. Tokens are preissued; login and password-hashing load are not measured.

## Measurement method

The generator uses a closed loop: each worker waits for its response and optional think time. It does not generate an independent open arrival rate. Six warmup checklist requests precede each run and are excluded. The active loop lasts 20 seconds; requests already in flight and a final monitoring probe drain before the measured end. Throughput is completed application requests divided by actual elapsed time, including that drain. The evaluator records p50, p95, p99, maximum and each request's actual status. Small sample counts make tail percentiles coarse; no confidence interval or sustained-capacity claim is made.

Readiness probes run separately approximately every two seconds after the preceding probe completes. Their requests are not counted in application throughput. Complete-response client latency includes network/proxy time, queueing and PHP work. The Server-Timing header and safe operations log provide separate application timing; these should not be confused with client end-to-end latency.

Windows Get-Process samples backend TotalProcessorTime, WorkingSet64 and PrivateMemorySize64 about once per second. CPU percent is CPU-time delta divided by sample-window elapsed time, expressed as one-core equivalent. The sampled RSS/private maximum is not an absolute operating-system high-water mark. Raw TLS proxy process samples are also retained. The generator, collector and application share the host, which limits external validity.

## Compared environments

| Property | Development | Controlled deployment surrogate |
|---|---|---|
| Transport | Local loopback HTTP | Local HTTPS through a separate Node TLS process |
| Laravel flags | local, debug enabled | production, debug disabled |
| Configuration and routes | Uncached | Generated config and route caches |
| API server | One PHP built-in worker | One PHP built-in worker |
| Database | Isolated SQLite fixture | Copy of the same fixture, separate file |
| TLS verification | Not applicable | Explicit local certificate trust; insecure mode false |

Host: Intel(R) Core(TM) i5-8250U CPU @ 1.60GHz; 8 logical processors; 31.9 GiB RAM; Windows 10.0.26300; PHP 8.2.12; Node v24.19.0. Both configurations use the same application revision, safe request logging, queue index, WAL and a 5000 ms SQLite busy timeout. The comparison changes a bundle of settings, so it does not isolate the causal effect of caching alone.
