# Week 16 Deployment Recovery and Readiness

Project 23 Enrollment Requirement Validator. Sam Russell M. So, BSIT-3A. 18 November 2026.

## Conditional recommendation

**Conditional GO for a supervised local demonstration using fictional data only. NO-GO for real enrollment, public deployment or promised peak capacity on the measured configuration.** This scope restriction is not a pass of the failed health/peak gates. All ordinary latency/throughput goals passed, but readiness latency passed 0 of eight runs, peak throughput only one of four, and peak latency only two of four. Zero unexpected errors in the short test does not establish an availability SLA.

## Actual failed-change and rollback drill

The evaluator stopped the controlled backend, checkpointed SQLite WAL and copied the stopped database. It preserved the previous generated configuration and routes. A candidate release named w16-bad was then started with a nonexistent SQLite path. Liveness returned 200 while readiness returned 503. This demonstrated why liveness alone cannot admit traffic after deployment.

The first observed fault response arrived 1.377 seconds after starting the candidate. Rollback restored the previous cached configuration and the database checkpoint with the backend stopped. Readiness recovered in 1.179 seconds after rollback began, within the pre-set 15-second target. The restored release was w16-r1; an authenticated enrollment request returned 200. The selected requirement record matched its checkpoint, including status and correction note.

| Check | Actual observation |
|---|---|
| Candidate release | w16-bad |
| Candidate liveness / readiness | 200 / 503 |
| Rollback recovery time | 1.179 seconds |
| Restored release / readiness | w16-r1 / 200 |
| Restored authenticated checklist | 200 |
| Stopped database copy hash | Exact match to checkpoint before requests |
| Selected business record | Preserved |

The checkpoint SHA-256 and restored-file SHA-256 were both a2103bec26e112dae96343b46697ca79a5d4d02eabd82847e572857285e0a24a. This is a configuration rollback and point-in-time data restore, not schema rollback or multi-host failover. No legitimate writes were admitted after the checkpoint; the result does not prove continuous production RPO=0. Restoring an older real backup could lose writes after its checkpoint, so an institution must define its own RPO and RTO.

## Before a real deployment

Use a production PHP web stack with reviewed worker capacity and OPcache, a patched supported runtime, trusted public TLS and explicit proxy trust. Establish institutional workloads and SLAs, repeat longer ordinary/peak tests from a separate load host, and keep health probes responsive under saturation. Review database choice and concurrent writers, backups, restore consistency, disk/log retention, secrets and monitoring alerts. Retest all deployment changes rather than treating this local surrogate as public infrastructure.

Retain the last compatible application artifact, lockfiles, generated environment-specific cache and a verified database backup. Exclude cached configuration and private keys from source/submissions. Deploy in maintenance or a controlled traffic-drain window; verify readiness and an authenticated smoke path before admitting traffic. On failure, stop writes, restore the matched configuration/artifact and an authorized checkpoint, restart, and verify release identity, readiness and business state. Preserve the incident and recovery record.

## Limits and next work

The test set is a controlled fictional prototype with two short repetitions per profile, one host, a single built-in PHP worker and preissued tokens. It excludes login storms, real document upload content, reminder workers, distributed failures, sustained soak tests and institutional UAT. Public registration/password recovery and the inherited reminder automation remain outside this slice. Week 17 should conduct representative UAT and complete documentation; Week 18 should demonstrate and defend the bounded solution. Instructor approval/signature remains an external review step.
