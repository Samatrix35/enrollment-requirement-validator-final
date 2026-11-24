$ErrorActionPreference='Stop'
Set-Location $PSScriptRoot
Push-Location enrollment_validator_api
try {
    php artisan test
    if ($LASTEXITCODE -ne 0) { throw 'Backend tests failed' }
    composer audit --locked
    if ($LASTEXITCODE -ne 0) { throw 'Dependency audit requires review' }
} finally { Pop-Location }
flutter analyze
if ($LASTEXITCODE -ne 0) { throw 'Flutter analysis failed' }
flutter test
if ($LASTEXITCODE -ne 0) { throw 'Flutter tests failed' }
