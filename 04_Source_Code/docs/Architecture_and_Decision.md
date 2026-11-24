# ADR-017 - Final architecture recommendation
Date: 2026-11-23 | Status: accepted for fictional local demonstration, deployment recommendation conditional.
Decision owner: student developer; institutional deployment authority is not assumed.

## Context
Project 23 needs two protected roles, transactional status changes, correction feedback and reproducible classroom evidence. Current measured host is a PHP development server on Windows with SQLite. No live university system, paid hosting or production administrator is authorized.

| Criterion | A: Laravel/SQLite local monolith | B: Laravel PHP-FPM/PostgreSQL behind TLS proxy |
|---|---|---|
| Functional fit | Existing implementation | Same API semantics, migration needed |
| Complexity/skills | Low, matches current student tools | Higher: proxy, process management, SQL server |
| Reliability | One host/worker; stopped-file recovery | Better separation possible; must engineer failover |
| Security | Roles and token controls; local HTTP demo | TLS and secret management required; more components |
| Cost | Existing local resources | Hosting, backups, monitoring and administration |
| Performance | Measured peak/health gaps | Concurrency options; not yet measured |
| Data ownership | Isolated fictional SQLite | Approved DB custody/retention and roles needed |
| Support/change | Simple lockfiles and local restore | Deployment/migration/on-call discipline required |

## Decision and consequences
Keep A for the bounded supervised demonstration. Evaluate B before real service. Avoid a microservice split: no independent domain/scale requirement justifies added network and ownership complexity. This comparison is an engineering judgement, not a benchmark of B. Revisit when real users, write concurrency, public access, backup obligations or multi-host availability become requirements. Exit gates: actual stakeholder UAT, approved data access, realistic load/soak tests, TLS/proxy trust verification, restore drill and assigned support owner.
