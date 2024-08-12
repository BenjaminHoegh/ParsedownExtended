---
title: Table of Contents (ToC)
---

# Table of Content (ToC)

## Description

ParsedownExtended facilitates the automatic creation of a Table of Contents (ToC) for your Markdown documents. This feature dynamically includes every heading in the document, streamlining the process by eliminating the need for manually adding anchors to each title. The ToC is generated automatically, enhancing navigability and structure in longer documents.

## Configuration Syntax

To enable or configure the ToC feature, use the `setSetting` method:

```php
$ParsedownExtended->setSetting('toc', (boolean|array) $value);
```

## Parameters

Configure ToC with these options:

- **headings** (array): Defines which heading levels to include in the ToC.
- **set_toc_tag** (string): Sets a custom markdown tag for generating the ToC.

## Generating ToC and Content Separately

ParsedownExtended offers methods to generate ToC and content separately:

- **contentsList()**: Returns just the "ToC" as an HTML `<ul>` list.
- **body()**: Parses content without `[toc]` tag.
- **text()**: Parses content with `[toc]` tag(s).

Example usage:

```php
$content = file_get_contents('sample.md');
$Parsedown = new ParsedownExtended();

$body = $Parsedown->body($content);
$toc = $Parsedown->contentsList();

echo $toc;  // Table of Contents
echo $body; // Main body content
```

## Examples

### Enable ToC

To enable the ToC feature:

```php
$ParsedownExtended->setSetting('toc', true);
```

### Customize ToC

Customize ToC with specific configurations:

```php
$ParsedownExtended->setSetting('toc', [
    'headings' => ['h1', 'h2', 'h3'], // Headings to include
    'set_toc_tag' => '[toc]', // Custom ToC tag
]);
```