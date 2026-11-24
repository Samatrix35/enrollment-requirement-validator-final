# Fresh Week 17 Verification Summary
Release w17-r1 | Developer-run checks | Captured 2026-11-23T14:26:26.174Z
Sam Russell M. So, BSIT-3A | Project 23

| Verification | Actual result | Source artifact |
|---|---|---|
| Laravel/PHPUnit | 32 tests, 120 assertions, zero failures | backend-tests.txt; backend-junit.xml |
| Flutter tests | 22 passed | flutter-tests.txt |
| Flutter analyze | No issues found | flutter-analyze.txt |
| Flutter web | Debug build with bundled resources successful | flutter-build.txt |
| Postman/Newman | 47 requests, 147 assertions, zero failures, TLS verification on | postman-results.json; postman-junit.xml |
| Chrome rehearsal | 9 developer checks, 13 screenshots, zero JS errors | browser-acceptance.json |
| API contract | 19 operations cover actual routes; all local references resolve | api-contract-check.json; api-routes.json |
| Current dependency audit | Unavailable: package registry DNS/network failed | composer-audit-unavailable.txt |
| Stakeholder UAT | Pending, no participant feedback/approval supplied | UAT plan and blank participant record |

## Interpretation
Passed technical checks support the bounded fictional workflow, not actual stakeholder satisfaction, full accessibility or public deployment readiness. AC06 ownership/roles are verified with API tests, not inferred from screenshots. Browser AC10 proves return to sign-in; API regression proves token revocation.

Week 16 dependency audit had no advisories at that time and is preserved under Week16_Baseline, but this week could not refresh the registry. No current clean-audit assertion is made. Pinned dependencies remain unchanged. Re-run composer audit when network access is available.

## Findings and retests
D01 before: required-note widget expectation failed; final widget/browser shows the visible message without sending a blank decision. D02 before: expected422 received200; final Laravel and new Newman request verify422 and unchanged state. D03: initial browser startup failed because CanvasKit CDN could not be fetched. Build-Local-Web.ps1 uses --no-web-resources-cdn, and the final compiled browser run succeeds with local resources. Initial failures are preserved and clearly labeled; final results are separate.

## Authoring environment
Flutter3.47.8 / Dart3.13.7, PHP8.2.12, Node24.19.0; exact revision in tool-versions.txt. The sandbox required a copied Flutter SDK/Pub cache and a test-only fopen readability adapter; ordinary installations can use php artisan test. Framework metadata caches were initialized from the same locked dependency set. Early missing-cache/key and SDK-directory setup errors are recorded separately; no application assertion was bypassed. Intermediate regression output also records an incorrect initial guard placement that was corrected before final tests. The final source places the empty-update guard only in RequirementController.update.
