# Recorded Week18 Verification
Release w18-final | Final developer rehearsal captured 2026-11-23T15:20:30.977Z
Project23 | Sam Russell M. So, BSIT-3A

| Check | Actual result | Primary evidence |
|---|---|---|
| Laravel/PHPUnit | 32 tests, 120 assertions, zero failures | backend-tests.txt; backend-junit.xml |
| Flutter tests | 22 passed | flutter-tests.txt |
| Flutter analysis | No issues found | flutter-analyze.txt |
| Web build | Debug build, bundled resources, successful | flutter-build.txt |
| Verified HTTPS API regression | 47 requests, 147 assertions, zero failures | postman-results.json; postman-junit.xml |
| Final Chrome rehearsal | 9 developer checks, 13 real screenshots, zero JS errors | final-demo.json; final-demo-run.txt |
| API contract | 19 implemented operations, local references resolve | api-contract-check.json |
| Failure/recovery | Live200/ready503 during failure, ready200 after restoration, business result retained | failure-recovery-demo.json |
| Current dependency audit | Unavailable - registry DNS lookup failed; no clean current result claimed | dependency-audit-attempt.txt |
| Stakeholder / panel / E3 | Genuine stakeholder acceptance pending; panel/viva/E3 outcomes not recorded | Prior_Evidence/Week17_UAT_Status; preparation guides |

## What the fresh recovery timing means
The observed configuration-restoration interval was 3046.2milliseconds, measured from the start of restoring the helper configuration until an observed ready response. It includes helper stop/start and UI/probe delays; it is not an isolated server startup benchmark or a production RTO guarantee. The student UI then confirmed1/3 approved after restoration. See JSON timestamps/status bodies. This is separate from the historical Week16 checkpoint/config rollback of1.179seconds.

## Reproduction and evidence limits
The browser rehearsal and HTTPS regression use separate local helper servers. The browser uses loopback HTTP; Newman uses a generated local certificate with TLS verification enabled. Screenshots are actual compiled Flutter Web captures, not AI-generated mockups or invented Postman/VS Code screenshots. VS Code tasks and a Postman-compatible collection are supplied; no manual editor/API-client GUI use is claimed. E3 practice is not an examination result.

Historical workload and acceptance-status artifacts remain dated under Prior_Evidence. There is no new Week18 load/soak benchmark, actual stakeholder feedback or public deployment. The latest dependency-audit attempt failed and remains open. Private credentials/configuration, DB files, token values and TLS private keys are excluded from delivery.

## Authoring execution details
Normal users can run php artisan test and the documented Flutter commands. The constrained authoring environment used a writable workspace SDK/Pub cache and a test-only fopen readability adapter because metadata ACL probes failed, with framework metadata caches from the same locked dependency set. No application assertion was bypassed. run-tests-readability.php is preserved for reproducibility; run it from the API directory only with the documented PHP disable_functions option. Exact runtime versions are in tool-versions.txt.

Authoring adapter command, from the API directory: `php -d disable_functions=is_readable ../../03_Logs/run-tests-readability.php --log-junit ../../03_Logs/backend-junit.xml`. Normal local installations should use `php artisan test`.
