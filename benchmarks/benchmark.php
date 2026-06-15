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
    'no-color',
    'help',
]);

if (isset($options['help'])) {
    echo <<<TXT
        Usage:
          composer benchmark -- [--iterations=50] [--warmup=3] [--path=benchmarks/tests] [--memory] [--no-color]

        Examples:
          composer benchmark
          composer benchmark -- --iterations=100 --no-color
          composer benchmark -- --path=benchmarks/tests --memory

        TXT;
    exit(0);
}

if (!class_exists('Parsedown') || !class_exists('ParsedownExtra') || !class_exists(ParsedownExtended::class)) {
    fwrite(STDERR, "Error: Parsedown, ParsedownExtra, and ParsedownExtended must be installed.\n");
    exit(1);
}

$iterations = max(1, (int) ($options['iterations'] ?? 50));
$warmup = max(0, (int) ($options['warmup'] ?? 3));
$path = (string) ($options['path'] ?? 'benchmarks/tests');
$testPath = isAbsolutePath($path) ? $path : $root . '/' . $path;
$useColor = !isset($options['no-color']);
$includeMemory = isset($options['memory']);

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
echo 'Files: ' . count($markdownFiles) . PHP_EOL . PHP_EOL;

$parserFactories = parserComparisonFactories();
$parserComparison = benchmarkParsers($markdownFiles, $parserFactories, $iterations, $warmup, $includeMemory);
printParserComparison($parserComparison, $parserFactories, $useColor, $includeMemory);

echo PHP_EOL;

$extensionFactories = extensionImpactFactories();
$extensionComparison = benchmarkParsers($markdownFiles, $extensionFactories, $iterations, $warmup, $includeMemory);
printExtensionImpact($extensionComparison, array_keys($extensionFactories), $useColor, $includeMemory);

/**
 * @return array<string, callable(): object>
 */
function parserComparisonFactories(): array
{
    return [
    'Parsedown' => function (): Parsedown {
        return new Parsedown();
    },
    'ParsedownExtra' => function (): ParsedownExtra {
        return new ParsedownExtra();
    },
    'ParsedownExtended' => function (): ParsedownExtended {
        return new ParsedownExtended();
    },
    ];
}

/**
 * @return array<string, callable(): object>
 */
function extensionImpactFactories(): array
{
    $optionalDisabled = optionalExtensionsDisabledConfig();
    $allEnabled = mergeConfig($optionalDisabled, allOptionalExtensionsEnabledConfig());

    $factories = [
        'Baseline parser' => function (): ParsedownExtra {
            return new ParsedownExtra();
        },
        'ParsedownExtended defaults' => function (): ParsedownExtended {
            return new ParsedownExtended();
        },
        'All optional disabled' => function () use ($optionalDisabled): ParsedownExtended {
            return new ParsedownExtended($optionalDisabled);
        },
        'All optional enabled' => function () use ($allEnabled): ParsedownExtended {
            return new ParsedownExtended($allEnabled);
        },
    ];

    foreach (individualExtensionConfigs() as $name => $config) {
        $scenarioConfig = mergeConfig($optionalDisabled, $config);
        $factories[$name] = function () use ($scenarioConfig): ParsedownExtended {
            return new ParsedownExtended($scenarioConfig);
        };
    }

    return $factories;
}

function optionalExtensionsDisabledConfig(): array
{
    return [
        'alerts' => false,
        'diagrams' => false,
        'emojis' => false,
        'emphasis' => [
            'insertions' => false,
            'keystrokes' => false,
            'mark' => false,
            'strikethroughs' => false,
            'subscript' => false,
            'superscript' => false,
        ],
        'headings' => [
            'auto_anchors' => false,
            'special_attributes' => false,
        ],
        'links' => [
            'external_links' => false,
        ],
        'lists' => [
            'tasks' => false,
        ],
        'math' => false,
        'smartypants' => false,
        'tables' => [
            'tablespan' => false,
        ],
        'toc' => false,
        'typographer' => false,
    ];
}

function allOptionalExtensionsEnabledConfig(): array
{
    return [
        'alerts' => true,
        'diagrams' => [
            'enabled' => true,
            'chartjs' => true,
            'mermaid' => true,
        ],
        'emojis' => true,
        'emphasis' => [
            'insertions' => true,
            'keystrokes' => true,
            'mark' => true,
            'strikethroughs' => true,
            'subscript' => true,
            'superscript' => true,
        ],
        'headings' => [
            'auto_anchors' => true,
            'special_attributes' => true,
        ],
        'links' => [
            'external_links' => true,
        ],
        'lists' => [
            'tasks' => true,
        ],
        'math' => true,
        'smartypants' => true,
        'tables' => [
            'tablespan' => true,
        ],
        'toc' => true,
        'typographer' => true,
    ];
}

function individualExtensionConfigs(): array
{
    return [
        'Abbreviations predefined' => [
            'abbreviations' => [
                'predefined' => [
                    'HTML' => 'HyperText Markup Language',
                ],
            ],
        ],
        'Alerts' => ['alerts' => true],
        'Diagrams' => [
            'diagrams' => [
                'enabled' => true,
                'chartjs' => true,
                'mermaid' => true,
            ],
        ],
        'Emoji' => ['emojis' => true],
        'External link processing' => [
            'links' => [
                'external_links' => true,
            ],
        ],
        'Heading anchors' => [
            'headings' => [
                'auto_anchors' => true,
            ],
        ],
        'Heading attributes' => [
            'headings' => [
                'special_attributes' => true,
            ],
        ],
        'Insertions' => [
            'emphasis' => [
                'insertions' => true,
            ],
        ],
        'Keystrokes' => [
            'emphasis' => [
                'keystrokes' => true,
            ],
        ],
        'Marking' => [
            'emphasis' => [
                'mark' => true,
            ],
        ],
        'Math' => ['math' => true],
        'Smartypants' => ['smartypants' => true],
        'Strikethrough' => [
            'emphasis' => [
                'strikethroughs' => true,
            ],
        ],
        'Subscript' => [
            'emphasis' => [
                'subscript' => true,
            ],
        ],
        'Superscript' => [
            'emphasis' => [
                'superscript' => true,
            ],
        ],
        'Tablespan' => [
            'tables' => [
                'tablespan' => true,
            ],
        ],
        'Task lists' => [
            'lists' => [
                'tasks' => true,
            ],
        ],
        'TOC' => [
            'toc' => true,
            'headings' => [
                'auto_anchors' => true,
            ],
        ],
        'Typographer' => ['typographer' => true],
    ];
}

function mergeConfig(array $base, array $overrides): array
{
    foreach ($overrides as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
            $base[$key] = mergeConfig($base[$key], $value);
            continue;
        }

        $base[$key] = $value;
    }

    return $base;
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

function benchmarkParsers(array $markdownFiles, array $parserFactories, int $iterations, int $warmup, bool $includeMemory): array
{
    $rows = [];
    $totals = [];

    foreach ($parserFactories as $parserName => $_factory) {
        $totals[$parserName] = ['time' => 0.0, 'memory' => 0];
    }

    foreach ($markdownFiles as $source => $markdown) {
        $rows[$source] = [];

        foreach ($parserFactories as $parserName => $factory) {
            $parser = $factory();
            $result = benchmarkParser($parser, $markdown, $iterations, $warmup, $includeMemory);
            $rows[$source][$parserName] = $result;
            $totals[$parserName]['time'] += $result['time'];
            $totals[$parserName]['memory'] += $result['memory'];
        }
    }

    $fileCount = count($markdownFiles);
    $averages = [];
    foreach ($totals as $parserName => $total) {
        $averages[$parserName] = [
            'time' => $total['time'] / $fileCount,
            'memory' => (int) round($total['memory'] / $fileCount),
        ];
    }

    return ['rows' => $rows, 'averages' => $averages];
}

function benchmarkParser($parser, string $markdown, int $iterations, int $warmup, bool $includeMemory): array
{
    for ($i = 0; $i < $warmup; $i++) {
        parseMarkdown($parser, $markdown);
    }

    gc_collect_cycles();

    if ($includeMemory && function_exists('memory_reset_peak_usage')) {
        memory_reset_peak_usage();
    }

    $memoryStart = $includeMemory ? memory_get_usage(false) : 0;
    $start = hrtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        parseMarkdown($parser, $markdown);
    }

    $elapsed = hrtime(true) - $start;
    $memory = 0;

    if ($includeMemory) {
        $memory = max(0, memory_get_peak_usage(false) - $memoryStart);
    }

    return [
        'time' => ($elapsed / 1000000000) / $iterations,
        'memory' => $memory,
    ];
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
            $cell = formatMs($times[$parserName]['time']);
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
        $cell = formatMs($comparison['averages'][$parserName]['time']);
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

/**
 * @param list<string> $scenarioNames
 */
function printExtensionImpact(array $comparison, array $scenarioNames, bool $useColor, bool $includeMemory): void
{
    echo colorize('Extension Impact', 'bold', $useColor) . PHP_EOL;

    $baselineName = 'Baseline parser';
    $baselineTime = $comparison['averages'][$baselineName]['time'];
    $baselineMemory = $comparison['averages'][$baselineName]['memory'];

    $headers = ['Scenario', 'Average', 'Overhead', 'Impact', 'Relative'];
    if ($includeMemory) {
        $headers[] = 'Memory delta';
    }

    $rows = [];
    foreach ($scenarioNames as $scenarioName) {
        $average = $comparison['averages'][$scenarioName];
        $time = $average['time'];
        $overhead = $time - $baselineTime;
        $impact = $baselineTime > 0 ? ($overhead / $baselineTime) * 100 : 0.0;

        $row = [
            $scenarioName,
            formatMs($time),
            formatSignedMs($overhead),
            formatSignedPercent($impact),
            $scenarioName === $baselineName ? 'baseline' : formatComparison($time, $baselineTime, $useColor),
        ];

        if ($includeMemory) {
            $row[] = formatSignedBytes($average['memory'] - $baselineMemory);
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
    return '~ ' . number_format($seconds * 1000, 2) . ' ms';
}

function formatSignedMs(float $seconds): string
{
    $sign = $seconds >= 0 ? '+' : '-';
    return $sign . number_format(abs($seconds) * 1000, 2) . ' ms';
}

function formatSignedPercent(float $percent): string
{
    $sign = $percent >= 0 ? '+' : '-';
    return $sign . number_format(abs($percent), 1) . '%';
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

function formatSignedBytes(int $bytes): string
{
    $sign = $bytes >= 0 ? '+' : '-';
    return $sign . formatBytes(abs($bytes));
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
