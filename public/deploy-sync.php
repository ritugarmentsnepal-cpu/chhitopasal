<?php
// Temporary deploy script - DELETE after use
// Triggered via: https://www.chhitopasal.com/deploy-sync.php

$secret = 'chhito2026deploy';
if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    die('Forbidden');
}

$projectDir = dirname(__DIR__);
chdir($projectDir);

$output = [];
$output[] = "Working dir: " . getcwd();

// Force sync with git
exec('git fetch origin main 2>&1', $gitFetch);
$output[] = "Git fetch: " . implode("\n", $gitFetch);

exec('git reset --hard origin/main 2>&1', $gitReset);
$output[] = "Git reset: " . implode("\n", $gitReset);

// Clear Laravel view cache
$viewsDir = $projectDir . '/storage/framework/views';
$files = glob($viewsDir . '/*.php');
$cleared = 0;
foreach ($files as $file) {
    if (unlink($file)) $cleared++;
}
$output[] = "Cleared {$cleared} cached views";

// Also try artisan
exec('php artisan view:clear 2>&1', $artisanOutput);
$output[] = "Artisan: " . implode("\n", $artisanOutput);

header('Content-Type: text/plain');
echo implode("\n\n", $output);
