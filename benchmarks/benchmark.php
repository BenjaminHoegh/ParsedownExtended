<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Erusev\Parsedown\State;
use Erusev\Parsedown\Parsedown;
use BenjaminHoegh\ParsedownExtended\ParsedownExtended;

// --- Benchmark Logic ---

const BENCHMARK_ITERATIONS = 10;
const MARKDOWN_TEST_DIR = 'tests';

function benchmarkParser($parser, string $markdown, int $iterations = BENCHMARK_ITERATIONS): float
{
    $totalTime = 0.0;
    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $parser->toHtml($markdown);
        $totalTime += microtime(true) - $start;
    }
    return $totalTime / $iterations;
}

function loadMarkdownFiles(string $dir = MARKDOWN_TEST_DIR): array
{
    $files = [
        'angular readme'   => "$dir/angular-readme.md",
        'bootstrap readme' => "$dir/bootstrap-readme.md",
        'homebrew readme'  => "$dir/homebrew-readme.md",
        'jquery readme'    => "$dir/jquery-readme.md",
        'markdown readme'  => "$dir/markdown-readme.md",
        'rails readme'     => "$dir/rails-readme.md",
        'textmate readme'  => "$dir/textmate-readme.md",
    ];

    $markdowns = [];
    $warnings = [];
    foreach ($files as $name => $path) {
        if (file_exists($path)) {
            $markdowns[$name] = file_get_contents($path);
        } else {
            $markdowns[$name] = '';
            $warnings[] = "Markdown file not found: " . htmlspecialchars($path);
        }
    }
    return [$markdowns, $warnings];
}

function getAvailableParsers(): array
{
    $parsedown = new Parsedown(new State());
    $parsedownextended = new Parsedown(ParsedownExtended::from(new State()));
    return [
        'Parsedown'         => $parsedown,
        'ParsedownExtended' => $parsedownextended,
    ];
}

function renderWarnings(array $warnings): string
{
    if (empty($warnings)) return '';
    $html = "<div class='warnings'><ul>";
    foreach ($warnings as $warning) {
        $html .= "<li style='color:red;'><strong>Warning:</strong> $warning</li>";
    }
    $html .= "</ul></div>";
    return $html;
}

function renderBenchmarkResults(array $markdownFiles, array $parsers): string
{
    $totals = [];
    $fileCount = count($markdownFiles);
    $html = '';

    foreach ($markdownFiles as $name => $markdown) {
        $html .= "<tr>";
        $html .= "<td><strong>" . htmlspecialchars($name) . "</strong></td>";

        $baseTime = benchmarkParser($parsers['Parsedown'], $markdown);
        $baseTimeMs = round($baseTime * 1000, 2);
        $totals['Parsedown'] = ($totals['Parsedown'] ?? 0) + $baseTime;
        $html .= "<td>~ <strong>{$baseTimeMs} ms</strong></td>";

        $time = benchmarkParser($parsers['ParsedownExtended'], $markdown);
        $timeMs = round($time * 1000, 2);
        $totals['ParsedownExtended'] = ($totals['ParsedownExtended'] ?? 0) + $time;

        $speedRatio = $time / $baseTime;
        $speedClass = $speedRatio < 1 ? 'faster' : 'slower';
        $times = round($speedRatio < 1 ? 1 / $speedRatio : $speedRatio, 2);

        $html .= "<td>~ <strong>{$timeMs} ms</strong> or <span class='{$speedClass}'>{$times} times</span> {$speedClass}</td>";
        $html .= "</tr>";
    }

    // Averages
    $html .= "<tr class='average'>";
    $html .= "<td><strong>Averages</strong></td>";
    $avgBase = $fileCount > 0 ? round(($totals['Parsedown'] / $fileCount) * 1000, 2) : 0;
    $html .= "<td>~ <strong>{$avgBase} ms</strong></td>";
    $avgTime = $fileCount > 0 ? round(($totals['ParsedownExtended'] / $fileCount) * 1000, 2) : 0;
    $avgRatio = $avgBase > 0 ? $avgTime / $avgBase : 0;
    $avgClass = $avgRatio < 1 ? 'faster' : 'slower';
    $avgTimes = $avgRatio > 0 ? round($avgRatio < 1 ? 1 / $avgRatio : $avgRatio, 1) : 0;
    $html .= "<td>~ <strong>{$avgTime} ms</strong> or <span class='{$avgClass}'>{$avgTimes} times</span> {$avgClass}</td>";
    $html .= "</tr>";

    return $html;
}

// --- Prepare Data ---
[$markdownFiles, $warnings] = loadMarkdownFiles();
$parsers = getAvailableParsers();
$resultsHtml = renderBenchmarkResults($markdownFiles, $parsers);
$warningsHtml = renderWarnings($warnings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Benchmarks</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>Performance Benchmarks</h1>
<?= $warningsHtml ?>
<table>
    <thead>
        <tr>
            <th><strong>Source</strong></th>
            <th><strong>Parsedown</strong> | base speed</th>
            <th><strong>ParsedownExtended</strong> | our extension</th>
        </tr>
    </thead>
    <tbody id="benchmark-results">
        <?= $resultsHtml ?>
    </tbody>
</table>
</body>
</html>
