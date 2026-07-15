<?php

declare(strict_types=1);

use BenjaminHoegh\ParsedownExtended\Configuration\Configuration;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param mixed $value
 */
function formatConfigurationDefault($value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_string($value)) {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    if (!is_array($value)) {
        return var_export($value, true);
    }

    $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
    $items = [];

    foreach ($value as $key => $item) {
        $formatted = formatConfigurationDefault($item);
        $items[] = $isList
            ? $formatted
            : formatConfigurationDefault($key) . ' => ' . $formatted;
    }

    return '[' . implode(', ', $items) . ']';
}

$header = <<<'MARKDOWN'
    # Configuration Reference

    All options are read and updated through the parser instance:

    ```php
    $parsedown = new BenjaminHoegh\ParsedownExtended\ParsedownExtended();
    $parsedown->config()->set('math', true);
    $parsedown->config()->set('links.external_links.open_in_new_window', false);
    ```

    Feature aliases such as `math`, `diagrams`, `links`, `toc`, or `headings.auto_anchors` map to their explicitly defined `*.enabled` option. For example, `config()->set('math', true)` is equivalent to `config()->set('math.enabled', true)`.

    This reference is generated from `Configuration::definitions()`. Add or change an option there rather than editing this table manually.

    ## Options

    | Path | Type | Default | Description |
    | --- | --- | --- | --- |
    MARKDOWN;

$rows = [];
foreach (Configuration::definitions() as $path => $definition) {
    $alias = $definition['alias'] === null
        ? ''
        : ' Alias: `' . $definition['alias'] . '`.';

    $rows[] = sprintf(
        '| `%s` | %s | `%s` | %s%s |',
        $path,
        $definition['type'],
        formatConfigurationDefault($definition['default']),
        $definition['description'],
        $alias
    );
}

$output = $header . "\n" . implode("\n", $rows) . "\n";
$documentationPath = dirname(__DIR__) . '/docs/configuration.md';

if (in_array('--check', $argv, true)) {
    $current = file_get_contents($documentationPath);
    if ($current !== $output) {
        fwrite(STDERR, "Configuration documentation is out of date. Run composer docs:configuration.\n");
        exit(1);
    }

    exit(0);
}

file_put_contents($documentationPath, $output);
