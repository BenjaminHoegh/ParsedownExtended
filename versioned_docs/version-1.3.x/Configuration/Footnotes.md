---
title: Footnotes
---

# Footnotes

## Description

Footnotes in Markdown are an elegant solution for adding notes, citations, or additional information without overcrowding the main text. ParsedownExtended supports footnotes, allowing you to insert a superscript number linked to the footnote content at the bottom of the page. This feature enhances document readability, enabling readers to easily access related notes and references.

## Configuration Syntax

To manage footnotes in your documents using ParsedownExtended, utilize the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for footnotes:

```php
$configValue = $ParsedownExtended->config()->get('footnotes');
```

### Setting the Configuration

To activate or deactivate the footnotes feature:

```php
$ParsedownExtended->config()->set('footnotes', (bool) $value);
```

This setting allows you to enable or disable footnotes based on your documentation needs.

## Examples

### Enable Footnotes

Footnotes are enabled by default. To explicitly ensure they are enabled:

```php
$ParsedownExtended->config()->set('footnotes', true);
```

### Disable Footnotes

To disable footnotes, preventing the parsing and rendering of footnote syntax:

```php
$ParsedownExtended->config()->set('footnotes', false);
```

By configuring the footnotes feature, you can tailor how your Markdown content utilizes footnotes, providing a cleaner and more informative reading experience. Whether enabling detailed references or maintaining a streamlined document body, ParsedownExtended offers the flexibility to suit your formatting preferences.