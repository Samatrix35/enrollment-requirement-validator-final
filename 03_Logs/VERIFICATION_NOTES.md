# Verification provenance

All Week 15 evidence was generated for Project 23 on 18 November 2026, Asia/Manila. JSON/HTTP timestamps may use UTC (+8 hours gives Manila). Week 14 source was extracted from the supplied enrollment archive; friend-format documents were references only.

Backend baseline: the 16 new backend tests ran against the inherited source; one failed (duplicate assignment). Client baseline: 10 API contract tests ran against inherited source; four failed (D01–D04). Screen tests subsequently reproduced validation overflow and an unhandled expiry future. These observed failures are preserved before the final 23-test Laravel and 19-test Flutter passes. Baseline and final assertion counts differ because more assertions execute after an early failure is fixed.

The Windows sandbox makes PHP is_readable return false for files that can actually be opened. The test-only harness uses fopen to verify real readability; no assertions, authentication checks or business behavior are bypassed. The shipped application and dependencies are not patched for that environment. Normal terminals can run php artisan test directly. Windows sandbox cache preflight errors were environment setup failures, not counted as product defects.

Flutter API tests use MockClient for deterministic malformed, failed and stalled responses. Screen tests exercise the real widget state machine with controlled API doubles. Newman exercises the real Laravel HTTP API and SQLite database over verified local HTTPS. Browser tests exercise the compiled Flutter app against Laravel, with a controlled browser offline state for network failure and a fixture-only token deletion for revocation. These are not student UAT sessions or production penetration tests.

Postman results are Newman collection runs, not screenshots of Postman's desktop GUI. Screenshots are actual Chrome captures; any verification-summary image is explicitly labeled as a rendering of the saved results. No AI-generated evidence or borrowed screenshots are used.

The dependency audit result is time-specific. Local TLS uses a private fixture key kept outside the submission. The clean ZIP excludes .env, SQLite data, bearer tokens, vendor, node_modules, generated Flutter outputs, and private certificates. Log records include event names/IDs only; saved API results omit token values.
