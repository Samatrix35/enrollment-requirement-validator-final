# Week 16 setup and reproduction

Open the source workspace in VS Code. Run Setup-Demo.ps1 in 04_Source_Code to restore Composer/Flutter lockfile dependencies, create private .env configuration and seed fictional demo accounts. Setup does not erase an existing database. Use the workspace tasks for Laravel on 8046 and Flutter on 5046. The Flutter command includes `--dart-define=API_BASE_URL=http://127.0.0.1:8046/api`.

## Health and regression

```powershell
# API terminal
php artisan serve --host=127.0.0.1 --port=8046

# Client terminal
flutter run -d chrome --web-port=5046 --dart-define=API_BASE_URL=http://127.0.0.1:8046/api

# Source root
node tools/check-health.cjs
flutter analyze
flutter test

# API directory
php artisan test
composer audit --locked
```

The staff screen has Check service health. `/api/health/live` is process/application liveness. `/api/health/ready` checks the database and required schema. Both are minimal public signals; production health requests require HTTPS. Neither should expose paths, configuration, records or secrets.

## Reproduce workloads without deleting demo data

Stop listeners on 8046, 8047 and 8447 before running `tools/Run-Week16-Evaluation.ps1`. It creates a new tools/runtime-* directory and new SQLite fixtures; it never calls migrate:fresh on the application database. The fixture command refuses production environments and requires an explicit fixture flag set only by the evaluator. Its low-cost synthetic password hashing is for fixture generation only, outside load measurement; ordinary account hashing is unchanged.

The harness requires Windows PowerShell for real process counters, PHP, Node.js and OpenSSL. It detects the installed XAMPP OpenSSL path used here; set PHP_BIN, OPENSSL_BIN and OPENSSL_CONF for another installation. Both configurations use separate SQLite files, the same source and one PHP server worker. The controlled server uses generated production config/route caches and a local certificate explicitly trusted by the runner. No insecure TLS option or OS/browser trust-store modification is used.

Generated configuration, fixture tokens, database backups and private keys stay in ignored runtime storage and must not be committed. New measurement output defaults to an ignored timestamped directory. Pass `-OutputDirectory` for another writable results directory. A successful process exit means the evaluator completed; individual performance gates may still be false.

## Postman

The Week 16 collection adds health, trace and release checks to the inherited enrollment regression scenarios. It expects a fresh disposable seeded demo (IDs 1-3). Do not run a reset against valuable data. The separate `tools/Run-HTTPS-Checks.ps1 -ResetDemo` wrapper explicitly resets the generated local demo only after that switch is supplied, creates a short-lived local certificate and runs Newman with verification enabled. It uses ports 8048/8448. The workload evaluator is separate and does not reset the demo database.

## Deployment rollback

Use an authorized backup and matched release before any real change. For this evaluation, the backend was stopped and SQLite WAL checkpointed before copying. A bad configuration was rejected by readiness, then the old configuration and checkpoint were restored. The benchmark logs describe the actual observed scope and timings. Copying a database while active writers continue is not the tested procedure. Do not infer zero production data loss, schema rollback or high availability from this drill.

## Optional figure reproduction

The plots are already included. To regenerate them, install the optional Python packages in tools/requirements-plot.txt and run `python tools/plot-evaluation.py --results <completed-results-directory> --output <new-plot-directory>`. It reads actual completed workload-summary.json and rollback-recovery.json records. Choose a new output directory to preserve the submitted evidence. The plot dependency is not part of the Laravel application runtime.

The evaluated API health endpoints are /api/health/live and /api/health/ready. Laravel's inherited /up is a basic framework liveness signal and is not the readiness gate used here. The evaluator explicitly supplies APP_ENV, APP_DEBUG and TRUSTED_PROXIES to the server processes, including when configuration is cached; production hosting must supply and review equivalent process-level settings.
