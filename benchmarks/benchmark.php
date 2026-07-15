#!/usr/bin/env php
<?php

declare(strict_types=1);

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    fwrite(STDERR, "Error: vendor/autoload.php not found. Run composer install first.\n");
    exit(1);
}

require_once $autoload;

$options = getopt('', [
    'iterations::',
    'warmup::',
    'path::',
    'memory',
    'mode::',
    'no-color',
    'help',
]);

if (isset($options['help'])) {
    echo <<<TXT
        Usage:
                    composer benchmark -- [--iterations=50] [--warmup=3] [--mode=reuse|fresh] [--path=benchmarks/tests] [--memory] [--no-color]

        Examples:
          composer benchmark
          composer benchmark -- --iterations=100 --no-color
                    composer benchmark -- --mode=fresh
          composer benchmark -- --path=benchmarks/tests --memory

        TXT;
    exit(0);
}

if (!class_exists('ParsedownExtra') || !class_exists(ParsedownExtended::class)) {
    fwrite(STDERR, "Error: ParsedownExtra and ParsedownExtended must be installed.\n");
    exit(1);
}

$iterations = max(1, (int) ($options['iterations'] ?? 50));
$warmup = max(0, (int) ($options['warmup'] ?? 3));
$path = (string) ($options['path'] ?? 'benchmarks/tests');
$testPath = isAbsolutePath($path) ? $path : $root . '/' . $path;
$useColor = !isset($options['no-color']);
$includeMemory = isset($options['memory']);
$mode = (string) ($options['mode'] ?? 'reuse');

if (!in_array($mode, ['reuse', 'fresh'], true)) {
    fwrite(STDERR, "Error: --mode must be either 'reuse' or 'fresh'.\n");
    exit(1);
}

if (extension_loaded('xdebug')) {
    fwrite(STDERR, "Warning: Xdebug is enabled. Benchmark results may be inaccurate.\n\n");
}

$markdownFiles = loadMarkdownFiles($testPath);
if ($markdownFiles === []) {
    fwrite(STDERR, "Error: No benchmark markdown files found in {$testPath}\n");
    exit(1);
}

echo colorize('Performance Benchmarks', 'bold', $useColor) . PHP_EOL;
echo "Iterations: {$iterations}" . PHP_EOL;
echo "Warmup: {$warmup}" . PHP_EOL;
echo 'Mode: ' . ($mode === 'reuse' ? 'reuse parser' : 'construct parser for every parse') . PHP_EOL;
echo 'Files: ' . count($markdownFiles) . PHP_EOL . PHP_EOL;

$parserFactories = [
    'ParsedownExtra' => function (): ParsedownExtra {
        return new ParsedownExtra();
    },
    'ParsedownExtended' => function (): ParsedownExtended {
        return new ParsedownExtended();
    },
];

$comparison = benchmarkParsers($markdownFiles, $parserFactories, $iterations, $warmup, $includeMemory, $mode);
printParserComparison($comparison, $parserFactories, $useColor, $includeMemory);

function isAbsolutePath(string $path): bool
{
    return $path !== '' && ($path[0] === '/' || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1);
}

function loadMarkdownFiles(string $testPath): array
{
    $allFiles = [];
    foreach (glob(rtrim($testPath, '/\\') . '/*.md') ?: [] as $file) {
        $name = basename($file, '.md');
        $contents = file_get_contents($file);
        if (is_string($contents)) {
            $allFiles[$name] = $contents;
        }
    }

    ksort($allFiles);

    return $allFiles;
}

function benchmarkParsers(array $markdownFiles, array $parserFactories, int $iterations, int $warmup, bool $includeMemory, string $mode): array
{
    $rows = [];
    $totals = [];

    foreach ($parserFactories as $parserName => $_factory) {
        $totals[$parserName] = ['time' => 0.0, 'p95' => 0.0, 'memory' => 0];
    }

    foreach ($markdownFiles as $source => $markdown) {
        $rows[$source] = [];

        foreach ($parserFactories as $parserName => $factory) {
            // ParsedownExtra retains a private footnote counter when reused. It
            // remains useful as a timing baseline, but only ParsedownExtended is
            // expected to guarantee stable output in reuse mode.
            $verifyOutputStability = $mode === 'fresh' || $parserName !== 'ParsedownExtra';
            $result = benchmarkParser(
                $factory,
                $markdown,
                $iterations,
                $warmup,
                $includeMemory,
                $mode,
                $verifyOutputStability
            );
            $rows[$source][$parserName] = $result;
            $totals[$parserName]['time'] += $result['time'];
            $totals[$parserName]['p95'] += $result['p95'];
            $totals[$parserName]['memory'] += $result['memory'];
        }
    }

    $fileCount = count($markdownFiles);
    $averages = [];
    foreach ($totals as $parserName => $total) {
        $averages[$parserName] = [
            'time' => $total['time'] / $fileCount,
            'p95' => $total['p95'] / $fileCount,
            'memory' => (int) round($total['memory'] / $fileCount),
        ];
    }

    return ['rows' => $rows, 'averages' => $averages];
}

function benchmarkParser(
    callable $factory,
    string $markdown,
    int $iterations,
    int $warmup,
    bool $includeMemory,
    string $mode,
    bool $verifyOutputStability
): array {
    $parser = $mode === 'reuse' ? $factory() : null;
    $expected = null;

    for ($i = 0; $i < $warmup; $i++) {
        $warmupParser = $mode === 'reuse' ? $parser : $factory();
        $output = parseMarkdown($warmupParser, $markdown);
        $expected ??= $output;
        if ($verifyOutputStability) {
            assertStableOutput($expected, $output);
        }
    }

    gc_collect_cycles();

    if ($includeMemory && function_exists('memory_reset_peak_usage')) {
        memory_reset_peak_usage();
    }

    $memoryStart = $includeMemory ? memory_get_usage(false) : 0;
    $samples = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = hrtime(true);
        $iterationParser = $mode === 'reuse' ? $parser : $factory();
        $output = parseMarkdown($iterationParser, $markdown);
        $samples[] = (hrtime(true) - $start) / 1000000000;

        $expected ??= $output;
        if ($verifyOutputStability) {
            assertStableOutput($expected, $output);
        }
    }
    $memory = 0;

    if ($includeMemory) {
        $memory = max(0, memory_get_peak_usage(false) - $memoryStart);
    }

    return [
        'time' => percentile($samples, 0.50),
        'p95' => percentile($samples, 0.95),
        'memory' => $memory,
    ];
}

function assertStableOutput(string $expected, string $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Parser output changed between benchmark iterations.');
    }
}

function percentile(array $samples, float $percentile): float
{
    sort($samples, SORT_NUMERIC);
    $index = (int) ceil($percentile * count($samples)) - 1;
    return $samples[max(0, min($index, count($samples) - 1))];
}

function parseMarkdown($parser, string $markdown): string
{
    return $parser->text($markdown);
}

function printParserComparison(array $comparison, array $parserFactories, bool $useColor, bool $includeMemory): void
{
    echo colorize('Parser Comparison', 'bold', $useColor) . PHP_EOL;

    $headers = array_merge(['Source'], array_keys($parserFactories));
    $rows = [];

    foreach ($comparison['rows'] as $source => $times) {
        $baseTime = $times['ParsedownExtra']['time'];
        $row = [$source];

        foreach (array_keys($parserFactories) as $parserName) {
            $cell = formatMs($times[$parserName]['time']) . ' / p95 ' . formatMs($times[$parserName]['p95']);
            if ($parserName !== 'ParsedownExtra') {
                $cell .= ' / ' . formatComparison($times[$parserName]['time'], $baseTime, $useColor);
            }
            if ($includeMemory) {
                $cell .= ' / ' . formatBytes($times[$parserName]['memory']);
            }
            $row[] = $cell;
        }

        $rows[] = $row;
    }

    $averageBaseTime = $comparison['averages']['ParsedownExtra']['time'];
    $averageRow = ['Averages'];
    foreach (array_keys($parserFactories) as $parserName) {
        $cell = formatMs($comparison['averages'][$parserName]['time']) . ' / p95 ' . formatMs($comparison['averages'][$parserName]['p95']);
        if ($parserName !== 'ParsedownExtra') {
            $cell .= ' / ' . formatComparison($comparison['averages'][$parserName]['time'], $averageBaseTime, $useColor);
        }
        if ($includeMemory) {
            $cell .= ' / ' . formatBytes($comparison['averages'][$parserName]['memory']);
        }
        $averageRow[] = $cell;
    }
    $rows[] = $averageRow;

    printTable($headers, $rows);
}

function colorize(string $text, string $color, bool $enabled): string
{
    if (!$enabled) {
        return $text;
    }

    switch ($color) {
        case 'green':
            return "\033[32m{$text}\033[0m";
        case 'red':
            return "\033[31m{$text}\033[0m";
        case 'bold':
            return "\033[1m{$text}\033[0m";
        default:
            return $text;
    }
}

function visibleLength(string $value): int
{
    return strlen(preg_replace('/\033\[[0-9;]*m/', '', $value));
}

function padVisible(string $value, int $length): string
{
    return $value . str_repeat(' ', max(0, $length - visibleLength($value)));
}

function formatMs(float $seconds): string
{
    return number_format($seconds * 1000, 2) . ' ms';
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }

    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }

    return $bytes . ' B';
}

function formatComparison(float $time, float $baseTime, bool $useColor): string
{
    if ($baseTime <= 0 || $time <= 0) {
        return '';
    }

    $ratio = $time / $baseTime;

    if (abs($ratio - 1.0) < 0.01) {
        return 'same speed';
    }

    if ($ratio < 1) {
        return colorize(number_format(1 / $ratio, 2) . 'x faster', 'green', $useColor);
    }

    return colorize(number_format($ratio, 2) . 'x slower', 'red', $useColor);
}

function formatImprovement(float $time, float $baseTime, bool $useColor): string
{
    if ($baseTime <= 0 || $time <= 0) {
        return '';
    }

    $ratio = $time / $baseTime;

    if (abs($ratio - 1.0) < 0.01) {
        return 'same speed';
    }

    if ($ratio < 1) {
        return colorize(number_format((1 - $ratio) * 100, 1) . '% faster', 'green', $useColor);
    }

    return colorize(number_format(($ratio - 1) * 100, 1) . '% slower', 'red', $useColor);
}

function printTable(array $headers, array $rows): void
{
    $widths = [];
    foreach ($headers as $index => $header) {
        $widths[$index] = visibleLength($header);
    }

    foreach ($rows as $row) {
        foreach ($row as $index => $cell) {
            $widths[$index] = max($widths[$index] ?? 0, visibleLength($cell));
        }
    }

    printTableRow($headers, $widths);

    $dividers = [];
    foreach ($widths as $width) {
        $dividers[] = str_repeat('-', $width);
    }
    echo implode('-+-', $dividers) . PHP_EOL;

    foreach ($rows as $row) {
        printTableRow($row, $widths);
    }
}

function printTableRow(array $row, array $widths): void
{
    foreach ($row as $index => $cell) {
        echo padVisible($cell, $widths[$index]);

        if ($index < count($row) - 1) {
            echo ' | ';
        }
    }

    echo PHP_EOL;
}
