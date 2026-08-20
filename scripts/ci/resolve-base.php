<?php

declare(strict_types=1);

function resolveCiBaseSha(): string
{
    $base = trim(getenv('CI_BASE_SHA') ?: '');

    if (isUsableCiBase($base)) {
        return $base;
    }

    foreach (['HEAD^', 'origin/dev'] as $fallback) {
        $resolved = trim((string) shell_exec(
            'git rev-parse --verify '.escapeshellarg($fallback).' 2>&1'
        ));

        if (isUsableCiBase($resolved)) {
            return $resolved;
        }
    }

    fwrite(STDERR, "Unable to resolve a base commit for the CI diff.\n");
    exit(2);
}

function isUsableCiBase(string $base): bool
{
    if ($base === '' || preg_match('/^0+$/', $base) === 1) {
        return false;
    }

    exec(
        'git cat-file -e '.escapeshellarg($base.'^{commit}').' 2>&1',
        $output,
        $exitCode
    );

    return $exitCode === 0;
}