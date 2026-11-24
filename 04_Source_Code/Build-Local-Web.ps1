$ErrorActionPreference='Stop'
Set-Location $PSScriptRoot
flutter build web --no-pub --debug --no-web-resources-cdn --dart-define=API_BASE_URL=http://127.0.0.1:8046/api
if ($LASTEXITCODE -ne 0) { throw 'Local web build failed' }
Write-Host 'Built local web resources. Start the API, then run node tools/serve-web.cjs and visit http://127.0.0.1:5046.'

