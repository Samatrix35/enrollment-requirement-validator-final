# Week 16 evidence provenance

All Week 16 measurements, plots and screenshots were generated for Project 23 on 18 November 2026. Raw JSON timestamps and HTTP dates use UTC; add eight hours for Asia/Manila. The supplied Week 15 source is the continuation baseline. Its images, run results and reports were not reused as Week 16 measurements.

The initial evaluator setup found that this Laravel build treated Windows drive-letter APP_CONFIG_CACHE paths as relative. The evaluator was corrected to use API-relative cache paths, then the entire planned eight-run measurement executed from a new isolated fixture. The initial setup error is retained separately and is not counted as an application request/performance failure. No timed run was discarded or replaced, and thresholds stayed fixed.

The Windows sandbox reports false for PHP is_readable on some files that can actually be opened. The test-only fopen-based readability harness was used for PHPUnit. No assertion or application authorization was bypassed. The published normal command remains php artisan test. The source application's dependency code was not changed for that workaround.

Client timings cover each complete HTTP response. Throughput includes actual elapsed drain time. Warmup is excluded. Monitoring probes are counted separately. 320 application requests completed with 0 unexpected statuses/transport errors; this is a finite observation, not an availability guarantee. CPU means use Windows TotalProcessorTime deltas over the sampled window. WorkingSet64 and PrivateMemorySize64 are process samples, not PHP heap or total machine usage. The TLS proxy is sampled separately in raw data.

The monitored candidate deployment deliberately used a missing database path. Its liveness/readiness split, configuration restoration, stopped-server copy hashes and selected record were observed, not fabricated. RTO was measured from rollback start to readiness recovery. No legitimate traffic was accepted after the chosen checkpoint, so the drill cannot establish production RPO=0.

Postman evidence is an actual Newman collection run over strictly verified local HTTPS, not a Postman desktop GUI screenshot. App evidence is actual Chrome rendering of the compiled Flutter client. Plots and the evaluation dashboard are explicitly labeled as visualizations derived from saved measurements; they are not screenshots of a live production monitoring service. No images were generated with an image generator.

All three prior security/functional test layers were rerun. The final browser record contains 4 checks, 7 application captures and no JavaScript errors. Unit/widget tests use controlled doubles where described in the test source; the workload, Postman and browser runs use the real Laravel API and SQLite. Generated .env, caches, credentials, tokens, private certificates, runtime folders and database snapshots are excluded from the clean ZIP.
