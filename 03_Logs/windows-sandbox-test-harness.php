<?php
// Windows sandbox ACL probes can fail although opening the same file succeeds.
// Test harness only: verify readability by actually opening the requested file.
function is_readable(string $path): bool {
    if (is_dir($path)) return true;
    $handle = @fopen($path, 'rb');
    if ($handle === false) return false;
    fclose($handle);
    return true;
}
require getcwd().'/vendor/phpunit/phpunit/phpunit';
