<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Benchmarks</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
            color: #777;
            font-family: "Roboto", "lucida grande", tahoma, verdana, arial, sans-serif;
            font-size: 16px;
            line-height: 1.5rem;
            margin: 0;
            padding: 0;
            font-weight: 400;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 20px;
            text-align: left;
            font-weight: normal;
        }

        td {
            width: 20%;
        }

        table th:first-child, table td:first-child {
            width: 20%;
        }

        tbody tr:nth-child(odd) {
            background-color: #1D1D1D;
        }

        .average {
            border-top: 1px solid #333;
        }

        strong {
            color: #ddd;
            font-weight: 400;
            font-size: 16px;
        }

        .faster {
            color: #77dd77;
        }
        .slower {
            color: #dd7777;
        }

        .xdebug-warning {
            background-color: #ffcc00;
            color: #333;
            padding: 10px;
            margin: 20px 0;
            font-size: 16px;
            font-weight: 400;
        }

        .error {
            color: #dd7777;
            padding: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>

<h1>Performance Benchmarks</h1>

<?php
# Load the autoloader first
require_once '../vendor/autoload.php';

# Check if xdebug is enabled
if (extension_loaded('xdebug')) {
    echo "<div class='xdebug-warning'>Warning: Xdebug is enabled. This may result in incorrect benchmark results.</div>";
}

// Ensure ParsedownExtra and ParsedownExtended are available
if (!class_exists('ParsedownExtra') || !class_exists('BenjaminHoegh\ParsedownExtended\ParsedownExtended')) {
    echo "<div class='error'>Error: ParsedownExtra and ParsedownExtended are required but not found. Please make sure these packages are installed.</div>";
    exit; // Stop further execution
}

?>

<table>
    <thead>
        <tr>
            <th><strong>Source</strong></th>
            <th><strong>ParsedownExtra</strong> | base speed </th>
            <th><strong>ParsedownExtended</strong> | our extension </th>
            <th><strong>Markdown PHP</strong> | the original parser </th>
            <th><strong>League</strong> | commonmark parser</th>
        </tr>
    </thead>

    <tbody id="benchmark-results">
<?php

// Define a function to measure execution time and average it over multiple runs
function benchmark($parser, $markdown, $iterations = 10)
{
    $total_time = 0;

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);

        if ($parser instanceof Michelf\Markdown) {
            $parser->defaultTransform($markdown);
        } elseif ($parser instanceof League\CommonMark\CommonMarkConverter) {
            $parser->convert($markdown);
        } else {
            $parser->text($markdown);
        }

        $total_time += microtime(true) - $start;
    }

    return $total_time / $iterations; // Return average time
}

// Load your markdown files (adjust paths as necessary)
$markdown_files = [
    'angular readme' => file_get_contents('tests/angular-readme.md'),
    'bootstrap readme' => file_get_contents('tests/bootstrap-readme.md'),
    'homebrew readme' => file_get_contents('tests/homebrew-readme.md'),
    'jquery readme' => file_get_contents('tests/jquery-readme.md'),
    'markdown readme' => file_get_contents('tests/markdown-readme.md'),
    'rails readme' => file_get_contents('tests/rails-readme.md'),
    'textmate readme' => file_get_contents('tests/textmate-readme.md'),
    // Add other markdown files similarly
];

// Initialize parsers
$parsers = [];
$parsers['ParsedownExtra'] = new ParsedownExtra();
$parsers['ParsedownExtended'] = new \BenjaminHoegh\ParsedownExtended\ParsedownExtended();

if (class_exists('Michelf\MarkdownExtra')) {
    $parsers['Markdown PHP'] = new \Michelf\MarkdownExtra();
}
if (class_exists('League\CommonMark\CommonMarkConverter')) {
    $parsers['League'] = new \League\CommonMark\CommonMarkConverter();
}

// Initialize variables to calculate averages
$totals = [];
$averages = [];
$file_count = count($markdown_files);

// Run the benchmarks
foreach ($markdown_files as $name => $markdown) {
    echo "<tr>";
    echo "<td><strong>$name</strong></td>";

    // Benchmark ParsedownExtra first to establish the base speed
    $parsedown_extra_time = benchmark($parsers['ParsedownExtra'], $markdown);
    $parsedown_extra_time_ms = round($parsedown_extra_time * 1000, 2); // Convert to milliseconds
    $totals['ParsedownExtra'] = ($totals['ParsedownExtra'] ?? 0) + $parsedown_extra_time;

    echo "<td>~ <strong>{$parsedown_extra_time_ms} ms</strong></td>";

    // Benchmark other parsers and compare to ParsedownExtra
    foreach ($parsers as $parser_name => $parser) {
        if ($parser_name !== 'ParsedownExtra') {
            $time = benchmark($parser, $markdown);
            $time_ms = round($time * 1000, 2); // Convert to milliseconds
            $totals[$parser_name] = ($totals[$parser_name] ?? 0) + $time;

            $speed_diff = $time / $parsedown_extra_time;
            $speed_text = $speed_diff < 1 ? 'faster' : 'slower';
            $times_diff = round($speed_diff < 1 ? 1 / $speed_diff : $speed_diff, 2);

            echo "<td>~ <strong>{$time_ms} ms</strong> or <span class='{$speed_text}'>{$times_diff} times</span> {$speed_text}</td>";
        }
    }

    echo "</tr>";
}

// Calculate the averages
echo "<tr class='average'>";
echo "<td><strong>Averages</strong></td>";

// Average for ParsedownExtra
$average_parsedown_extra_time = round(($totals['ParsedownExtra'] / $file_count) * 1000, 2);
echo "<td>~ <strong>{$average_parsedown_extra_time} ms</strong></td>";

// Averages for other parsers relative to ParsedownExtra
foreach ($parsers as $parser_name => $parser) {
    if ($parser_name !== 'ParsedownExtra') {
        $average_time = round(($totals[$parser_name] / $file_count) * 1000, 2);
        $average_speed_diff = $average_time / $average_parsedown_extra_time;
        $average_speed_text = $average_speed_diff < 1 ? 'faster' : 'slower';
        $average_times_diff = round($average_speed_diff < 1 ? 1 / $average_speed_diff : $average_speed_diff, 1);

        echo "<td>~ <strong>{$average_time} ms</strong> or <span class='{$average_speed_text}'>{$average_times_diff} times</span> {$average_speed_text}</td>";
    }
}

echo "</tr>";
?>
    </tbody>
</table>
</body>
</html>
