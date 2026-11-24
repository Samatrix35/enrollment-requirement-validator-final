# Enrollment Requirement Validator - Final Week 18 source
Project 23 | Sam Russell M. So, BSIT-3A | Release w18-final

Flutter Web + Laravel/Sanctum + SQLite. The same bounded checklist/review workflow is finalized, with retained security, validation and health controls. Start with docs/Setup_and_Reproduction.md. API8046; built web5046. Setup-Demo.ps1 installs dependencies and makes a fictional local DB; Build-Local-Web.ps1 bundles web resources. VS Code workspace/tasks are included. The Postman collection is postman/Week18_Final_Defense.postman_collection.json.

Run php artisan test in enrollment_validator_api, flutter test/analyze here, and the documented HTTPS regression. Node tools are optional; npm ci installs pinned Newman. Browser rehearsal additionally needs Playwright 1.62.1 and Chrome; use node tools/run-week18-demo.cjs --reset-demo only on disposable fictional data after a debug build. It owns the helper servers and writes the final workflow/recovery evidence. Do not start conflicting demo servers at the same time.

docs/openapi.json is the machine-readable contract; check-api-contract.cjs verifies route coverage/local references. User_Guide, Data_Model, Architecture_and_Decision and Operations_and_Maintenance explain the implementation and handover. Test filenames from prior weeks remain because those regression requirements are still applicable; execution logs are fresh Week 18 runs.

This is not a production registrar service. Stakeholder UAT/approval is pending; historical performance/health limits still apply. Current dependency advisories must be refreshed when registry access is available. Private .env, DB files, vendor/node_modules, build output and TLS keys are excluded; reproduce from lockfiles and setup.

This final submission targets Flutter Web only. Unneeded Android/iOS/desktop scaffold folders are omitted from the delivery.
