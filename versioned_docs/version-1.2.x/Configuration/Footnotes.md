---
title: Footnotes
---

# Footnotes

## Description

Footnotes in Markdown are an elegant solution for adding notes, citations, or additional information without overcrowding the main text. ParsedownExtended supports footnotes, allowing you to insert a superscript number linked to the footnote content at the bottom of the page. This feature enhances document readability, enabling readers to easily access related notes and references.

## Configuration Syntax

To manage footnotes in your documents using ParsedownExtended, utilize the `setSetting` method:

```php
$ParsedownExtended->setSetting('footnotes', (boolean) $value);
```

This setting enables you to activate or deactivate the footnotes feature, depending on your documentation needs.

## Examples

### Enable Footnotes

Footnotes are enabled by default. To explicitly ensure they are enabled:

```php
$ParsedownExtended->setSetting('footnotes', true);
```

### Disable Footnotes

To disable footnotes, preventing the parsing and rendering of footnote syntax:

```php
$ParsedownExtended->setSetting('footnotes', false);
```

By configuring the footnotes feature, you can tailor how your Markdown content utilizes footnotes, providing a cleaner and more informative reading experience. Whether enabling detailed references or maintaining a streamlined document body, ParsedownExtended offers the flexibility to suit your formatting preferences. If there are additional features or configurations you'd like to explore, please let me know how I can assist further!