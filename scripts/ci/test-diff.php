<?php

declare(strict_types=1);

require_once __DIR__.'/resolve-base.php';

$base = resolveCiBaseSha();

$changedFilesInput = getenv('CI_CHANGED_FILES') ?: '';

if ($changedFilesInput === '') {
    $changedFilesInput = (string) shell_exec('git diff --name-only --diff-filter=ACMRT '.escapeshellarg($base).' HEAD');
}

$changedFiles = array_values(array_filter(preg_split('/\R/', trim($changedFilesInput)) ?: []));

$testTargets = [
    'tests/Unit/BasicTest.php',
];

$addTarget = function (string $target) use (&$testTargets): void {
    if (is_file($target) || is_dir($target)) {
        $testTargets[] = $target;
    }
};

$addAllKnownModuleTests = function () use ($addTarget): void {
    foreach (['tests/Unit', 'tests/Feature'] as $target) {
        $addTarget($target);
    }
};

$mappedModules = [
    'AiAssistant'          => ['tests/Unit/AiAssistant', 'tests/Feature/AiAssistant'],
    'Auth'                 => ['tests/Unit/Auth', 'tests/Feature/Auth'],
    'BusCompanyManagement' => ['tests/Feature/BusCompanyManagement'],
    'BusManagement'        => ['tests/Feature/BusManagement'],
    'BookingManagement'    => ['tests/Feature/BookingManagement'],
    'Cancellation'         => ['tests/Feature/Cancellation'],
    'CustomerManagement'   => ['tests/Feature/CustomerManagement'],
    'Dashboard'            => ['tests/Feature/Dashboard'],
    'Notification'         => ['tests/Unit/Notification', 'tests/Feature/Notification'],
    'Payment'              => ['tests/Feature/Payment'],
    'Profile'              => ['tests/Unit/Profile', 'tests/Feature/Profile'],
    'Reporting'            => ['tests/Feature/Reporting'],
    'RolePermission'       => ['tests/Unit/RolePermission', 'tests/Feature/RolePermission'],
    'RouteManagement'      => ['tests/Feature/RouteManagement'],
    'SeatAvailability'     => ['tests/Feature/SeatAvailability'],
    'SeatManagement'       => ['tests/Feature/SeatManagement'],
    'SystemSetting'        => ['tests/Feature/SystemSetting'],
    'TicketManagement'     => ['tests/Feature/TicketManagement'],
    'TripManagement'       => ['tests/Feature/TripManagement'],
    'TripSearch'           => ['tests/Feature/TripSearch'],
    'UserManagement'       => ['tests/Feature/UserManagement'],
];

$addModuleTests = function (string $module) use ($addTarget, $addAllKnownModuleTests, $mappedModules): void {
    if (! array_key_exists($module, $mappedModules)) {
        $addAllKnownModuleTests();

        return;
    }

    foreach ($mappedModules[$module] as $target) {
        $addTarget($target);
    }
};

$isSharedCode = function (string $file): bool {
    if (str_starts_with($file, 'app/')
        || str_starts_with($file, 'bootstrap/')
        || str_starts_with($file, 'config/')
        || str_starts_with($file, 'database/migrations/')
        || str_starts_with($file, 'database/seeders/')
        || str_starts_with($file, 'routes/')
        || str_starts_with($file, 'packages/FuteBus/Core/')
        || in_array($file, ['composer.json', 'composer.lock', 'tests/Pest.php', 'tests/TestCase.php'], true)
    ) {
        return true;
    }

    if (preg_match('#^packages/(FuteBus|Customer)/[^/]+/src/(Models|Traits|Contracts|Repositories|Services)/#', $file)) {
        return true;
    }

    return false;
};

foreach ($changedFiles as $file) {
    if ($file === '') {
        continue;
    }

    if ($isSharedCode($file)) {
        $addAllKnownModuleTests();

        continue;
    }

    if (str_starts_with($file, 'tests/') && str_ends_with($file, 'Test.php') && is_file($file)) {
        $addTarget($file);

        continue;
    }

    if (preg_match('#^packages/(?:FuteBus|Customer)/([^/]+)/#', $file, $matches)) {
        $addModuleTests($matches[1]);

        continue;
    }
}

$testTargets = array_values(array_unique($testTargets));

usort($testTargets, static fn (string $left, string $right): int => strlen($left) <=> strlen($right));

$selectedTargets = [];

foreach ($testTargets as $target) {
    $normalizedTarget = rtrim(str_replace('\\', '/', $target), '/');
    $coveredByDirectory = false;

    foreach ($selectedTargets as $selectedTarget) {
        if (
            is_dir($selectedTarget)
            && (
                $normalizedTarget === $selectedTarget
                || str_starts_with($normalizedTarget, $selectedTarget.'/')
            )
        ) {
            $coveredByDirectory = true;

            break;
        }
    }

    if (! $coveredByDirectory) {
        $selectedTargets[] = $normalizedTarget;
    }
}

$testTargets = $selectedTargets;

echo "Running impacted PHPUnit targets:\n";

foreach ($testTargets as $target) {
    echo "- {$target}\n";
}

if (filter_var(getenv('CI_TEST_DIFF_DRY_RUN') ?: false, FILTER_VALIDATE_BOOL)) {
    exit(0);
}

$command = 'php artisan test --colors=always '.implode(' ', array_map('escapeshellarg', $testTargets));
passthru($command, $exitCode);

exit($exitCode);