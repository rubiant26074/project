<?php

$commands = [
    ['name' => 'server', 'command' => 'php artisan serve'],
    ['name' => 'queue', 'command' => 'php artisan queue:listen --tries=1 --timeout=0'],
    ['name' => 'vite', 'command' => PHP_OS_FAMILY === 'Windows' ? 'npm.cmd run dev' : 'npm run dev'],
];

if (function_exists('pcntl_fork')) {
    array_splice($commands, 2, 0, [[
        'name' => 'logs',
        'command' => 'php artisan pail --timeout=0',
    ]]);
}

$palette = ['#93c5fd', '#c4b5fd', '#fb7185', '#fdba74'];
$binary = PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx';
$segments = [
    escapeshellcmd($binary),
    'concurrently',
    '-c',
    escapeshellarg(implode(',', array_slice($palette, 0, count($commands)))),
];

foreach ($commands as $command) {
    $segments[] = escapeshellarg($command['command']);
}

$segments[] = '--names';
$segments[] = escapeshellarg(implode(',', array_column($commands, 'name')));
$segments[] = '--kill-others';

$commandLine = implode(' ', $segments);

if (in_array('--dry-run', $argv, true)) {
    fwrite(STDOUT, $commandLine.PHP_EOL);
    exit(0);
}

passthru($commandLine, $exitCode);

exit($exitCode);
