# Verification provenance

Runs were performed on 17 November 2026, Asia/Manila. Raw application log and API capture timestamps may be UTC; 14:xx UTC corresponds to 22:xx Manila.

The Laravel test runner initially encountered a Windows sandbox ACL issue: PHP is_readable returned false although fopen and require succeeded for the same file. The final run used PHP -d disable_functions=is_readable with a test-only harness that checks readability by actually opening the file. No application authorization or test assertions were bypassed. The harness is included alongside this note for reproducibility. Application dependencies were not modified. Seven tests and 32 assertions passed.

The live API run used Node fetch against the real Laravel HTTP server and SQLite database. It did not mock responses. The 26 saved responses redact access_token. The Postman collection was independently executed through Newman, with 26 requests, 26 assertions and no failures. The Newman CLI log, sanitized JSON results and JUnit XML are included. No Postman GUI execution is claimed.

Flutter was analyzed, widget tested, and built using the installed SDK. Screenshots are captured from Chrome rendering the actual compiled Flutter debug app connected to Laravel. They are not AI-generated images. Screenshots use fictional records. The browser log identifies the completed workflow and any JavaScript errors.

The submission ZIP excludes .env, SQLite database files, vendor, Flutter build/cache folders, and earlier screenshots. It preserves lockfiles, source, logs, and newly captured evidence. Setup reconstructs local runtime files.

Browser verification found and corrected two setState callbacks that returned Futures. The final browser capture uses the rebuilt source; browser-check.json records final JavaScript errors, if any. Screenshots include the actual corrected submission state, staff correction note, student feedback, and mobile viewport.
