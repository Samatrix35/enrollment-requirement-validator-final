# Setup and reproduction
## Prerequisites
Windows PowerShell, PHP8.2+ with SQLite/PDO, Composer2, Flutter stable with web, Chrome, Node18+ and npm. Tested host used PHP8.2.12 and Flutter3.47.8 (see actual logs for authoritative versions). Install from official tooling sources; lockfiles specify package versions.

## First run
1. Extract this package; open 04_Source_Code/EnrollmentValidator.code-workspace in VS Code if desired.
2. Run `powershell -ExecutionPolicy Bypass -File .\Setup-Demo.ps1` inside 04_Source_Code. It installs dependencies, makes framework directories, generates a private APP_KEY, migrates and seeds fictional demo accounts. It does not need any real university data.
3. In enrollment_validator_api run `php artisan serve --host=127.0.0.1 --port=8046`.
4. In a second source terminal run `flutter run -d chrome --web-hostname 127.0.0.1 --web-port 5046 --dart-define=API_BASE_URL=http://127.0.0.1:8046/api`.
5. Sign in as student.demo@example.test / StudentDemo-2026! or staff.demo@example.test / StaffDemo-2026! for fictional local use only. Never deploy these fixed demonstration credentials.

## Regression
Backend: `php artisan test` from API folder. Flutter: `flutter test`, `flutter analyze`, `flutter build web --debug --no-web-resources-cdn --dart-define=API_BASE_URL=http://127.0.0.1:8046/api`. For release use an approved HTTPS API and production TLS termination, not the local HTTP demo command.
Postman: import postman/Week17_Enrollment_Acceptance.postman_collection.json. Set base_url and temporary demo passwords locally. The scripted HTTPS runner under tools resets the generated demo DB only with explicit --reset-demo. Run `npm ci` for optional Node tools, then tools/Run-HTTPS-Checks.ps1 -ResetDemo. Read its documented reset behavior before use.
Browser: `node tools/run-week17-browser.cjs --reset-demo` after a debug web build; requires Chrome and optional Playwright1.62.1 (`npm install --no-save --package-lock=false playwright@1.62.1` after npm ci). It runs against the freshly seeded disposable demo DB, captures screenshots and records a workflow rehearsal; use only fictional data. Source tests and recorded results are separate from stakeholder feedback.

## Environment-specific execution notes
The automated authoring sandbox required a workspace copy of the Flutter SDK cache and a test-only PHP fopen readability adapter because ACL metadata probes failed. The adapter changes file-readability detection only, not assertions or application behavior. Normal local installations should use standard commands. No dependencies/private caches/build output or DB are distributed. See 03_Logs for exact commands/outcomes and any setup errors; do not count harness setup failures as product regressions.

## Offline-friendly local demonstration
Run Build-Local-Web.ps1, start the Laravel API, then run `node tools/serve-web.cjs` and visit http://127.0.0.1:5046. Bundling CanvasKit avoids the CDN dependency that failed during initial rehearsal. This debug HTTP setup is local-only. The automated browser runner includes --reset-demo explicitly because it reseeds its disposable demo database.
