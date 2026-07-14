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
    'no-feature-groups',
    'help',
]);

if (isset($options['help'])) {
    echo <<<TXT
        Usage:
          composer benchmark -- [--iterations=50] [--warmup=3] [--mode=reuse|fresh] [--path=benchmarks/tests] [--memory] [--no-feature-groups] [--no-color]

        Examples:
          composer benchmark
          composer benchmark -- --iterations=100 --no-color
          composer benchmark -- --mode=fresh --no-feature-groups
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
$includeFeatureGroups = !isset($options['no-feature-groups']);
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

if ($includeFeatureGroups) {
    echo PHP_EOL;
    $extraAverage = $comparison['averages']['ParsedownExtra']['time'];
    $featureGroups = benchmarkFeatureGroups($markdownFiles, $iterations, $warmup, $includeMemory);
    printFeatureGroups($featureGroups, $extraAverage, $useColor, $includeMemory);
}

function isAbsolutePath(string $path): bool
{
    return $path !== '' && ($path[0] === '/' || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1);
}

function loadMarkdownFiles(string $testPath): array
{
    $preferredOrder = [
        'angular-readme',
        'bootstrap-readme',
        'homebrew-readme',
        'jquery-readme',
        'markdown-readme',
        'rails-readme',
        'textmate-readme',
    ];

    $files = [];
    foreach ($preferredOrder as $name) {
        $file = rtrim($testPath, '/\\') . '/' . $name . '.md';
        if (is_file($file)) {
            $contents = file_get_contents($file);
            if (is_string($contents)) {
                $files[$name] = $contents;
            }
        }
    }

    if ($files !== []) {
        return $files;
    }

    foreach (glob(rtrim($testPath, '/\\') . '/*.md') ?: [] as $file) {
        $contents = file_get_contents($file);
        if (is_string($contents)) {
            $files[basename($file, '.md')] = $contents;
        }
    }

    ksort($files);

    return $files;
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
            $result = benchmarkParser($factory, $markdown, $iterations, $warmup, $includeMemory, $mode);
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

function benchmarkFeatureGroups(array $markdownFiles, int $iterations, int $warmup, bool $includeMemory): array
{
    $groups = featureGroupSettings();
    $results = [];

    foreach ($groups as $groupName => $settings) {
        $parser = new ParsedownExtended();
        applySettings($parser, $settings);

        $totalTime = 0.0;
        $totalMemory = 0;

        foreach ($markdownFiles as $markdown) {
            $result = benchmarkParser(static function () use ($parser) { return $parser; }, $markdown, $iterations, $warmup, $includeMemory, 'reuse');
            $totalTime += $result['time'];
            $totalMemory += $result['memory'];
        }

        $results[$groupName] = [
            'time' => $totalTime / count($markdownFiles),
            'memory' => (int) round($totalMemory / count($markdownFiles)),
        ];
    }

    return $results;
}

function featureGroupSettings(): array
{
    $optionalDisabled = [
        'toc' => false,
        'smartypants' => false,
        'typographer' => false,
        'emojis' => false,
        'math' => false,
        'diagrams' => false,
        'tables.tablespan' => false,
        'lists.tasks' => false,
        'links.external_links' => false,
        'abbreviations' => false,
        'headings.auto_anchors' => false,
        'emphasis.mark' => false,
        'emphasis.insertions' => false,
        'emphasis.keystrokes' => false,
        'emphasis.subscript' => false,
        'emphasis.superscript' => false,
    ];

    $allEnabled = [
        'abbreviations' => true,
        'abbreviations.allow_custom' => true,
        'alerts' => true,
        'allow_raw_html' => true,
        'code' => true,
        'code.blocks' => true,
        'code.inline' => true,
        'comments' => true,
        'definition_lists' => true,
        'diagrams' => true,
        'diagrams.chartjs' => true,
        'diagrams.mermaid' => true,
        'emojis' => true,
        'emphasis' => true,
        'emphasis.bold' => true,
        'emphasis.italic' => true,
        'emphasis.insertions' => true,
        'emphasis.keystrokes' => true,
        'emphasis.mark' => true,
        'emphasis.strikethroughs' => true,
        'emphasis.subscript' => true,
        'emphasis.superscript' => true,
        'footnotes' => true,
        'headings' => true,
        'headings.auto_anchors' => true,
        'headings.auto_anchors.lowercase' => true,
        'headings.auto_anchors.transliterate' => true,
        'headings.special_attributes' => true,
        'images' => true,
        'links' => true,
        'links.email_links' => true,
        'links.external_links' => true,
        'links.external_links.nofollow' => true,
        'links.external_links.noopener' => true,
        'links.external_links.noreferrer' => true,
        'links.external_links.open_in_new_window' => true,
        'lists' => true,
        'lists.tasks' => true,
        'math' => true,
        'math.block' => true,
        'math.inline' => true,
        'quotes' => true,
        'references' => true,
        'smartypants' => true,
        'smartypants.smart_angled_quotes' => true,
        'smartypants.smart_backticks' => true,
        'smartypants.smart_dashes' => true,
        'smartypants.smart_ellipses' => true,
        'smartypants.smart_quotes' => true,
        'tables' => true,
        'tables.tablespan' => true,
        'thematic_breaks' => true,
        'toc' => true,
        'typographer' => true,
    ];

    return [
        'default configuration' => [],
        'all settings enabled' => $allEnabled,
        'all optional disabled' => $optionalDisabled,
        'TOC disabled' => ['toc' => false],
        'smartypants disabled' => ['smartypants' => false],
        'typographer disabled' => ['typographer' => false],
        'emoji disabled' => ['emojis' => false],
        'math disabled' => ['math' => false],
        'diagrams disabled' => ['diagrams' => false],
        'tablespan disabled' => ['tables.tablespan' => false],
        'task lists disabled' => ['lists.tasks' => false],
        'external links disabled' => ['links.external_links' => false],
        'abbreviations disabled' => ['abbreviations' => false],
        'heading anchors disabled' => ['headings.auto_anchors' => false],
        'only headings/anchors' => array_merge($optionalDisabled, ['headings.auto_anchors' => true]),
        'only TOC' => array_merge($optionalDisabled, ['toc' => true, 'headings.auto_anchors' => true]),
        'only emoji' => array_merge($optionalDisabled, ['emojis' => true]),
        'only smartypants' => array_merge($optionalDisabled, ['smartypants' => true]),
        'only typographer' => array_merge($optionalDisabled, ['typographer' => true]),
        'only math' => array_merge($optionalDisabled, ['math' => true]),
        'only external links' => array_merge($optionalDisabled, ['links.external_links' => true]),
        'only abbreviations' => array_merge($optionalDisabled, ['abbreviations' => true]),
    ];
}

function applySettings(ParsedownExtended $parser, array $settings): void
{
    foreach ($settings as $path => $value) {
        $parser->config()->set($path, $value);
    }
}

function benchmarkParser(callable $factory, string $markdown, int $iterations, int $warmup, bool $includeMemory, string $mode): array
{
    $parser = $mode === 'reuse' ? $factory() : null;
    $expected = null;

    for ($i = 0; $i < $warmup; $i++) {
        $warmupParser = $mode === 'reuse' ? $parser : $factory();
        $output = parseMarkdown($warmupParser, $markdown);
        $expected = $expected ?? $output;
        assertStableOutput($expected, $output);
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

        $expected = $expected ?? $output;
        assertStableOutput($expected, $output);
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

function printFeatureGroups(array $featureGroups, float $extraAverage, bool $useColor, bool $includeMemory): void
{
    echo colorize('ParsedownExtended Feature Groups', 'bold', $useColor) . PHP_EOL;

    $defaultTime = $featureGroups['default configuration']['time'];
    $headers = ['Case', 'Average', 'vs default', 'vs ParsedownExtra'];
    if ($includeMemory) {
        $headers[] = 'Peak memory';
    }

    $rows = [];
    foreach ($featureGroups as $case => $result) {
        $row = [
            $case,
            formatMs($result['time']),
            $case === 'default configuration'
                ? 'baseline'
                : formatImprovement($result['time'], $defaultTime, $useColor),
            formatComparison($result['time'], $extraAverage, $useColor),
        ];

        if ($includeMemory) {
            $row[] = formatBytes($result['memory']);
        }

        $rows[] = $row;
    }

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
