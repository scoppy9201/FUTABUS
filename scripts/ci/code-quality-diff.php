<?php

declare(strict_types=1);

require_once __DIR__.'/resolve-base.php';

$base = resolveCiBaseSha();
$reportPath = getenv('CODE_QUALITY_REPORT') ?: 'code-quality-report.md';

$diff = filter_var(getenv('CI_DIFF_STDIN') ?: false, FILTER_VALIDATE_BOOLEAN)
    ? stream_get_contents(STDIN)
    : getenv('CI_DIFF_INPUT');

if ($diff === false) {
    $diff = (string) shell_exec('git diff --unified=0 --diff-filter=ACMRT '.escapeshellarg($base).' HEAD');
}

$hardFailures = [];
$warnings = [];
$currentFile = null;
$newLine = 0;
$addedCodeLines = [];
$removedLines = collectRemovedLines($diff);

$codeExtensions = [
    'php',
    'blade.php',
    'js',
    'cjs',
    'mjs',
    'ts',
    'css',
    'scss',
    'sass',
];

foreach (preg_split('/\R/', $diff) as $line) {
    if (str_starts_with($line, '+++ b/')) {
        $currentFile = substr($line, 6);

        continue;
    }

    if (preg_match('/^@@ -\d+(?:,\d+)? \+(\d+)(?:,\d+)? @@/', $line, $matches) === 1) {
        $newLine = (int) $matches[1];

        continue;
    }

    if ($currentFile === null || str_starts_with($line, '+++')) {
        continue;
    }

    if (str_starts_with($line, '+')) {
        $content = substr($line, 1);
        $normalizedContent = normalizeCodeLine($content);

        if (($removedLines[$currentFile][$normalizedContent] ?? 0) > 0) {
            $removedLines[$currentFile][$normalizedContent]--;
            $newLine++;

            continue;
        }

        if (isCodeFile($currentFile, $codeExtensions)) {
            if (trim($content) !== '') {
                $addedCodeLines[$currentFile] = ($addedCodeLines[$currentFile] ?? 0) + 1;
            }

            checkHardRules($currentFile, $newLine, $content, $hardFailures);
            checkWarningRules($currentFile, $newLine, $content, $warnings);
        }

        $newLine++;
    }
}

foreach ($addedCodeLines as $file => $lineCount) {
    if ($lineCount > 400) {
        $hardFailures[] = [
            'rule'    => 'Oversized code change',
            'file'    => $file,
            'line'    => 1,
            'message' => "This PR adds {$lineCount} non-empty lines to one code file (limit: 400). Split the change into focused, reviewable units.",
        ];
    }
}

$report = buildReport($base, $hardFailures, $warnings);
file_put_contents($reportPath, $report);
echo $report;

exit(count($hardFailures) > 0 ? 1 : 0);

function collectRemovedLines(string $diff): array
{
    $removedLines = [];
    $currentFile = null;

    foreach (preg_split('/\R/', $diff) as $line) {
        if (str_starts_with($line, '+++ b/')) {
            $currentFile = substr($line, 6);

            continue;
        }

        if ($currentFile === null || ! str_starts_with($line, '-') || str_starts_with($line, '---')) {
            continue;
        }

        $normalized = normalizeCodeLine(substr($line, 1));
        $removedLines[$currentFile][$normalized] = ($removedLines[$currentFile][$normalized] ?? 0) + 1;
    }

    return $removedLines;
}

function normalizeCodeLine(string $line): string
{
    return trim((string) preg_replace('/\s+/', ' ', $line));
}

function isCodeFile(string $file, array $extensions): bool
{
    foreach ($extensions as $extension) {
        if (str_ends_with($file, '.'.$extension)) {
            return true;
        }
    }

    return false;
}

function checkHardRules(string $file, int $line, string $content, array &$hardFailures): void
{
    $isRuleDefinition = $file === 'scripts/ci/code-quality-diff.php'
        && str_contains($content, 'preg_match(');

    if (preg_match('/^(<<<<<<<|=======|>>>>>>>)(?:\s|$)/', $content) === 1) {
        $hardFailures[] = [
            'rule'    => 'Unresolved merge conflict',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Resolve all merge-conflict markers before merging.',
        ];
    }

    if (preg_match('/^\s*(\/\/|#|\/\*|\*|<!--)?\s*[=\-_#\/]{6,}.*$/', $content) === 1) {
        $hardFailures[] = [
            'rule'    => 'Decorative separator',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Long decorative separators make diffs noisy. Use a small heading/comment only when it adds context.',
        ];
    }

    if (preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $content) === 1) {
        $hardFailures[] = [
            'rule'    => 'Emoji in code logic',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Emoji in executable code/templates is blocked. Keep user-facing icons in the design/icon system.',
        ];
    }

    if (
        ! $isRuleDefinition
        && preg_match('/<'.'svg\b/i', $content) === 1
        && preg_match('#(^|/)(icons?|illustrations?)/#i', $file) !== 1
    ) {
        $hardFailures[] = [
            'rule'    => 'Hand-written inline icon',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Inline SVG is not allowed in templates or logic. Use the shared icon system or a reviewed asset/component under an icons/illustrations directory.',
        ];
    }

    if (preg_match('/\b(console\.log|var_dump|print_r|dd|dump|die)\s*\(/', $content) === 1) {
        $hardFailures[] = [
            'rule'    => 'Debug code',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Remove debug/dead code before merging.',
        ];
    }

    if (
        ! $isRuleDefinition
        && preg_match('/\b(eslint-disable|phpstan-ignore|phpcs:ignore|@ts-ignore|@noinspection)\b/i', $content) === 1
        && ! str_contains($content, 'CI-ALLOW(')
    ) {
        $hardFailures[] = [
            'rule'    => 'Unjustified quality-rule suppression',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Do not suppress analyzers silently. Fix the issue or add `CI-ALLOW(reason and ticket)` for an explicitly reviewed exception.',
        ];
    }

    if (
        ! $isRuleDefinition
        && preg_match('/(?:->skip\s*\(|markTestSkipped\s*\(|\b(?:it|test|describe)\.only\s*\(|\bfit\s*\(|\bfdescribe\s*\()/i', $content) === 1
    ) {
        $hardFailures[] = [
            'rule'    => 'Skipped or focused test',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Skipped/focused tests can hide regressions. Restore the complete test suite before merging.',
        ];
    }

    if (
        preg_match('/^\s*(?:\/\/|#)\s*(?:if|else|foreach|for|while|return|throw|try|catch|\$[\w]+\s*=|[\w.]+\s*\()/i', $content) === 1
    ) {
        $hardFailures[] = [
            'rule'    => 'Commented-out code',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Delete commented-out code. Git history already preserves it.',
        ];
    }

    if (mb_strlen($content) > 300 && ! preg_match('#^\s*(https?://|[\'"]https?://)#', $content)) {
        $hardFailures[] = [
            'rule'    => 'Unreadable long line',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Code line exceeds 300 characters. Refactor it into readable, reviewable statements.',
        ];
    }

    if (
        preg_match('/\b(TODO|FIXME|HACK)\b/i', $content) === 1
        && preg_match('/\b(TODO|FIXME|HACK)\s*(\([^)]+\)|\[[^\]]+\]|:\s*@[\w.-]+)/i', $content) !== 1
    ) {
        $hardFailures[] = [
            'rule'    => 'Ownerless TODO',
            'file'    => $file,
            'line'    => $line,
            'message' => 'TODO/FIXME/HACK needs an owner, for example TODO(@name): ...',
        ];
    }

    if (preg_match('/\bAPP_DEBUG\s*=\s*true\b|[\'"]?debug[\'"]?\s*=>\s*true\b/i', $content) === 1) {
        $hardFailures[] = [
            'rule'    => 'Debug mode enabled',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Do not commit enabled debug mode.',
        ];
    }
}

function checkWarningRules(string $file, int $line, string $content, array &$warnings): void
{
    if (preg_match('/\b(Schema::drop(?:IfExists)?|DROP\s+(?:TABLE|DATABASE))\b/i', $content) === 1) {
        $warnings[] = [
            'rule'    => 'Destructive database operation',
            'file'    => $file,
            'line'    => $line,
            'message' => 'Confirm this destructive database operation is intentional and safely reversible.',
        ];
    }
}

function buildReport(string $base, array $hardFailures, array $warnings): string
{
    $statusMessage = match (true) {
        count($hardFailures) > 0 => '> Auto-merge is blocked. Fix every hard failure below and push a new commit.',
        count($warnings) > 0     => '> No hard failure was found. Review the warnings below before merging.',
        default                  => '> Code-quality checks passed. This change is eligible for the remaining CI and auto-merge gates.',
    };

    $lines = [
        '<!-- futabus-ci-code-quality -->',
        '## Code Quality Diff Check',
        '',
        '- Diff base: `'.$base.'`',
        '- Hard failures: **'.count($hardFailures).'**',
        '- Warnings: **'.count($warnings).'**',
        '',
        $statusMessage,
        '',
    ];

    if (count($hardFailures) === 0 && count($warnings) === 0) {
        $lines[] = 'No diff-only code-quality issues found.';
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    if (count($hardFailures) > 0) {
        $lines[] = '### Hard Failures';
        foreach ($hardFailures as $issue) {
            $lines[] = '- `'.$issue['file'].':'.$issue['line'].'` **'.$issue['rule'].'**: '.$issue['message'];
        }
        $lines[] = '';
    }

    if (count($warnings) > 0) {
        $lines[] = '### Warnings';
        foreach ($warnings as $issue) {
            $lines[] = '- `'.$issue['file'].':'.$issue['line'].'` **'.$issue['rule'].'**: '.$issue['message'];
        }
        $lines[] = '';
    }

    return implode(PHP_EOL, $lines);
}