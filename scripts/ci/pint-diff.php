<?php

declare(strict_types=1);

require_once __DIR__.'/resolve-base.php';

$base = resolveCiBaseSha();

$changedFiles = (string) shell_exec('git diff --name-only --diff-filter=ACMRT '.escapeshellarg($base).' HEAD');
$phpFiles = [];

foreach (preg_split('/\R/', trim($changedFiles)) as $file) {
    if ($file === '' || ! str_ends_with($file, '.php') || ! is_file($file)) {
        continue;
    }

    $phpFiles[] = $file;
}

if ($phpFiles === []) {
    echo "No changed PHP files found for Pint.\n";
    exit(0);
}

echo "Running Pint on changed PHP files:\n";

foreach ($phpFiles as $file) {
    echo "- {$file}\n";
}

$pint = dirname(__DIR__, 2).'/vendor/bin/pint';
$command = escapeshellarg(PHP_BINARY)
    .' '.escapeshellarg($pint)
    .' --test '.implode(' ', array_map('escapeshellarg', $phpFiles));
passthru($command, $exitCode);

exit($exitCode);