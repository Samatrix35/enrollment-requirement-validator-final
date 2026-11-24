$ErrorActionPreference='Stop'
Set-Location $PSScriptRoot
function Run-Checked([scriptblock]$Command) { & $Command; if ($LASTEXITCODE -ne 0) { throw "Command failed with exit code $LASTEXITCODE" } }
Push-Location enrollment_validator_api
try {
    @('bootstrap/cache','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs') | ForEach-Object { New-Item -ItemType Directory -Force $_ | Out-Null }
    Run-Checked { composer install --no-interaction --prefer-dist }
    if (!(Test-Path .env)) {
        $text=Get-Content .env.example -Raw
        $text=$text -replace '(?m)^DEMO_STUDENT_PASSWORD=.*','DEMO_STUDENT_PASSWORD=StudentDemo-2026!' -replace '(?m)^DEMO_STAFF_PASSWORD=.*','DEMO_STAFF_PASSWORD=StaffDemo-2026!'
        $text=$text -replace '(?m)^FRONTEND_URLS=.*','FRONTEND_URLS=http://localhost:5035,http://127.0.0.1:5035'
        Set-Content .env $text
    }
    if ((Get-Content .env -Raw) -match '(?m)^APP_KEY=\s*$') { Run-Checked { php artisan key:generate } }
    if (!(Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File | Out-Null }
    Run-Checked { php artisan migrate --seed --force }
} finally { Pop-Location }
Run-Checked { flutter pub get }
Write-Host 'Setup complete. Use the VS Code tasks to start Laravel and Flutter.'
