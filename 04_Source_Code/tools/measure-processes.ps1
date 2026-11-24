param([string]$TargetsJson)
$targets=$TargetsJson | ConvertFrom-Json
while ($true) {
    foreach ($target in $targets) {
        $process=Get-Process -Id $target.id -ErrorAction SilentlyContinue
        if ($null -ne $process) {
            [PSCustomObject]@{
                timestamp=[DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds()
                role=$target.role
                process_id=$process.Id
                cpu_seconds=$process.TotalProcessorTime.TotalSeconds
                working_set_bytes=$process.WorkingSet64
                private_bytes=$process.PrivateMemorySize64
            } | ConvertTo-Json -Compress
        }
    }
    Start-Sleep -Milliseconds 1000
}
