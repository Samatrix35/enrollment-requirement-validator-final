param([switch]$ResetDemo)
$ErrorActionPreference='Stop'
if (!$ResetDemo) { throw 'This run resets the local demo SQLite database. After reviewing the setup guide and preserving needed data, rerun with -ResetDemo on disposable fictional data only.' }
Set-Location (Join-Path $PSScriptRoot '..')
npm.cmd install --no-audit --no-fund
if ($LASTEXITCODE -ne 0) { throw 'Newman install failed' }
New-Item -ItemType Directory -Force tools/certs | Out-Null
$opensslCommand=$env:OPENSSL_BIN
if (!$opensslCommand) {
    if (Test-Path -LiteralPath 'C:\xampp\apache\bin\openssl.exe') { $opensslCommand='C:\xampp\apache\bin\openssl.exe' }
    else { $opensslCommand='openssl' }
}
if (!$env:OPENSSL_CONF -and (Test-Path -LiteralPath 'C:\xampp\apache\conf\openssl.cnf')) { $env:OPENSSL_CONF='C:\xampp\apache\conf\openssl.cnf' }
& $opensslCommand req -x509 -newkey rsa:2048 -nodes -keyout tools/certs/localhost-key.pem -out tools/certs/localhost.pem -days 2 -subj '/CN=localhost' -addext 'subjectAltName=DNS:localhost,IP:127.0.0.1'
if ($LASTEXITCODE -ne 0) { throw 'Certificate generation failed; install OpenSSL or use its full executable path' }
node tools/verify-https.cjs --reset-demo
if ($LASTEXITCODE -ne 0) { throw 'HTTPS verification failed' }
