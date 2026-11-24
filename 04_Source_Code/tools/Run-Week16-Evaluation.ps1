param([string]$OutputDirectory)
$ErrorActionPreference='Stop'
Set-Location (Join-Path $PSScriptRoot '..')
if (!(Get-Command php -ErrorAction SilentlyContinue)) { throw 'PHP is required.' }
if (!(Get-Command node -ErrorAction SilentlyContinue)) { throw 'Node.js is required for the local evaluation harness.' }
if (!$OutputDirectory) { $OutputDirectory=Join-Path $PSScriptRoot ('runtime/results-'+(Get-Date -Format 'yyyyMMdd-HHmmss')) }
node tools/run-evaluation.cjs --output $OutputDirectory
if ($LASTEXITCODE -ne 0) { throw 'Evaluation did not complete. Inspect evaluation-error.json and preserve the failed attempt separately.' }
Write-Host "Measured evaluation results: $OutputDirectory"
