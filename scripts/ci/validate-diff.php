<?php

declare(strict_types=1);

require_once __DIR__.'/resolve-base.php';

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

$base = resolveCiBaseSha();
$changedFiles = changedFiles($base);
$errors = [];

foreach ($changedFiles as $file) {
    if (! is_file($file)) {
        continue;
    }

    validateConflictMarkers($file, $errors);

    if (str_ends_with($file, '.php')) {
        validatePhpSyntax($file, $errors);
    } elseif (str_ends_with($file, '.json')) {
        validateJsonSyntax($file, $errors);
    } elseif (preg_match('/\.ya?ml$/', $file) === 1) {
        if (! ensureYamlParserIsAvailable($errors)) {
            continue;
        }

        validateYamlSyntax($file, $errors);
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Changed-file validation failed:\n");

    foreach ($errors as $error) {
        fwrite(STDERR, "- {$error}\n");
    }

    exit(1);
}

echo 'Validated '.count($changedFiles)." changed files: no syntax errors or conflict markers found.\n";

function ensureYamlParserIsAvailable(array &$errors): bool
{
    if (class_exists(Yaml::class)) {
        return true;
    }

    $autoload = dirname(__DIR__, 2).'/vendor/autoload.php';

    if (! is_file($autoload)) {
        $errors[] = 'Composer dependencies are required to validate changed YAML files';

        return false;
    }

    require_once $autoload;

    return true;
}

function changedFiles(string $base): array
{
    $output = getenv('CI_CHANGED_FILES') ?: '';

    if ($output === '') {
        $output = (string) shell_exec(
            'git diff --name-only --diff-filter=ACMRT '.escapeshellarg($base).' HEAD'
        );
    }

    return array_values(array_filter(preg_split('/\R/', trim($output)) ?: []));
}

function validateConflictMarkers(string $file, array &$errors): void
{
    $handle = fopen($file, 'rb');

    if ($handle === false) {
        $errors[] = "{$file}: unable to read file";

        return;
    }

    $lineNumber = 0;

    while (($line = fgets($handle)) !== false) {
        $lineNumber++;

        if (preg_match('/^(<<<<<<<|=======|>>>>>>>)(?:\s|$)/', $line) === 1) {
            $errors[] = "{$file}:{$lineNumber}: unresolved merge-conflict marker";
        }
    }

    fclose($handle);
}

function validatePhpSyntax(string $file, array &$errors): void
{
    exec('php -l '.escapeshellarg($file).' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        $errors[] = "{$file}: PHP syntax error: ".implode(' ', $output);
    }
}

function validateJsonSyntax(string $file, array &$errors): void
{
    try {
        json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        $errors[] = "{$file}: invalid JSON: {$exception->getMessage()}";
    }
}

function validateYamlSyntax(string $file, array &$errors): void
{
    try {
        Yaml::parseFile($file);
    } catch (ParseException $exception) {
        $errors[] = "{$file}: invalid YAML: {$exception->getMessage()}";
    }
}