---
title: Table of Contents (ToC)
---

# Table of Contents (ToC)

## Description

ParsedownExtended facilitates the automatic creation of a Table of Contents (ToC) for your Markdown documents. This feature dynamically includes every heading in the document, streamlining the process by eliminating the need for manually adding anchors to each title. The ToC is generated automatically, enhancing navigability and structure in longer documents.

## Configuration Syntax

To enable or configure the ToC feature, use the `config()->set()` method:

```php
$ParsedownExtended->config()->set('toc', (bool|array) $value);
```

## Parameters

Configure ToC with these options:

- **headings** (array): Defines which heading levels to include in the ToC.
- **tag** (string): Sets a custom markdown tag for generating the ToC.
- **id** (string): Assigns a custom ID to the ToC container.

## Generating ToC and Content Separately

ParsedownExtended offers methods to generate the ToC and content separately:

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
$ParsedownExtended->config()->set('toc', true);
```

### Customize ToC

Customize ToC with specific configurations:

```php
$ParsedownExtended->config()->set('toc', [
    'headings' => ['h1', 'h2', 'h3'], // Headings to include
    'tag' => '[toc]', // Custom ToC tag
    'id' => 'table-of-contents', // Custom ToC ID
]);
```

This documentation ensures clarity by using the correct settings structure, allowing users to properly configure and use the Table of Contents feature within ParsedownExtended.