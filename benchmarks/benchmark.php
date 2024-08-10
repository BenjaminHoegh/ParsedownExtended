<?php

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;

// Autoload
require_once '../vendor/autoload.php';

// Define a function to measure execution time
function benchmark($parser, $markdown)
{
    $start = microtime(true);
    $parser->text($markdown);
    return microtime(true) - $start;
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

?>

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
            width: 35%;
        }

        table th:first-child, table td:first-child {
            width: 30%;
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
    </style>
</head>
<body>

<h1>Performance Benchmarks</h1>

<?php
# Check if xdebug is enabled
if (extension_loaded('xdebug')) {
    echo "<div class='xdebug-warning'>Warning: Xdebug is enabled. This may result in incorrect benchmark results.</div>";
}
?>

<table>
    <thead>
        <tr>
            <th><strong>Source</strong></th>
            <th><strong>Parsedown</strong> | the original parser </th>
            <th><strong>ParsedownExtended</strong> | the parser that we created </th>
        </tr>
    </thead>

    <tbody id="benchmark-results">
<?php


// Initialize parsers
$parsedown = new Parsedown();
$parsedownExtended = new ParsedownExtended();

// Initialize variables to calculate averages
$total_original_time = 0;
$total_extended_time = 0;
$file_count = count($markdown_files);

// Run the benchmarks
foreach ($markdown_files as $name => $markdown) {
    $original_time = benchmark($parsedown, $markdown);
    $extended_time = benchmark($parsedownExtended, $markdown);

    $total_original_time += $original_time;
    $total_extended_time += $extended_time;

    # Calculate the speed difference
    $speed_diff = $extended_time / $original_time;

    $original_time_ms = round($original_time * 1000, 1); // Convert to milliseconds
    $extended_time_ms = round($extended_time * 1000, 1); // Convert to milliseconds

    if ($speed_diff < 1) {
        // ParsedownExtended is faster
        $times_diff = round(1 / $speed_diff, 1);
        $speed_text = 'faster';
    } else {
        // ParsedownExtended is slower
        $times_diff = round($speed_diff, 1);
        $speed_text = 'slower';
    }

    echo "<tr>";
    echo "<td><strong>$name</strong></td>";
    echo "<td>~ <strong>{$original_time_ms} ms</strong></td>";
    echo "<td>~ <strong>{$extended_time_ms} ms</strong> or <span class='{$speed_text}'>{$times_diff} times</span> {$speed_text}</td>";
    echo "</tr>";
}

// Calculate the averages
$average_speed_diff = $total_extended_time / $total_original_time;
$average_original_time = round(($total_original_time / $file_count) * 1000, 1);
$average_extended_time = round(($total_extended_time / $file_count) * 1000, 1);

if ($average_speed_diff < 1) {
    // ParsedownExtended is faster
    $average_times_diff = round(1 / $average_speed_diff, 1);
    $average_speed_text = 'faster';
} else {
    // ParsedownExtended is slower
    $average_times_diff = round($average_speed_diff, 1);
    $average_speed_text = 'slower';
}

// Display the averages
echo "<tr class='average'>";
echo "<td><strong>Averages</strong></td>";
echo "<td>~ <strong>{$average_original_time} ms</strong></td>";
echo "<td>~ <strong>{$average_extended_time} ms</strong> or <span class='{$average_speed_text}'>{$average_times_diff} times</span> {$average_speed_text}</td>";
echo "</tr>";

?>
    </tbody>
</table>
</body>
</html>
