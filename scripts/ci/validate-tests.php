<?php

declare(strict_types=1);

$errors = [];
$count = 0;

foreach (['Feature', 'Unit'] as $suite) {
    $directory = new RecursiveDirectoryIterator(__DIR__.'/../../tests/'.$suite);
    $files = new RecursiveIteratorIterator($directory);

    foreach ($files as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), 'Test.php')) {
            continue;
        }

        $count++;
        $relativePath = substr($file->getPathname(), strlen(__DIR__.'/../../tests/'));
        $parts = preg_split('#[\\\\/]#', $relativePath);

        if (count($parts) < 3) {
            $errors[] = $relativePath.': place tests in a feature/module directory';

            continue;
        }

        $expectedNamespace = 'Tests\\'.implode('\\', array_slice($parts, 0, -1));
        $expectedClass = substr($file->getBasename(), 0, -4);
        $source = file_get_contents($file->getPathname());

        if (! preg_match('/^namespace\s+'.preg_quote($expectedNamespace, '/').';/m', $source)) {
            $errors[] = $relativePath.': namespace must be '.$expectedNamespace;
        }

        if (! preg_match('/\bclass\s+'.preg_quote($expectedClass, '/').'\b/', $source)) {
            $errors[] = $relativePath.': class must match the file name';
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors).PHP_EOL);

    exit(1);
}

echo "Validated {$count} test files by feature/module.\n";
