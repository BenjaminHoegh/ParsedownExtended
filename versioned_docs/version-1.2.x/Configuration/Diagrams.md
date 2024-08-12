---
title: Diagrams
---

# Diagrams

## Description

ParsedownExtended introduces support for incorporating diagrams directly into Markdown documents, enhancing visual representation and understanding. This feature recognizes syntax intended for diagram rendering, specifically designed to work with [ChartJS](https://www.chartjs.org) and [Mermaid](https://mermaid-js.github.io/mermaid/). ParsedownExtended ensures that diagram code is preserved and remains unaltered for client-side rendering, requiring the inclusion of ChartJS and Mermaid JavaScript libraries in your project.

```mermaid
graph TD;
    A-->B;
    A-->C;
    B-->D;
    C-->D;
```

## Configuration Syntax

Enable the diagrams feature in ParsedownExtended using the `setSetting` method:

```php
$ParsedownExtended->setSetting('diagrams', (boolean) $value);
```

## Parameters

This feature allows the following configurations:

- **chartjs:** Enable or disable support for ChartJS diagrams.
- **mermaid:** Enable or disable support for Mermaid diagrams.

## Examples

### Enable Diagrams

To activate diagram support, ensuring that Markdown containing ChartJS or Mermaid syntax is properly recognized and left intact for client-side rendering:

```php
$ParsedownExtended->setSetting('diagrams', true);
```

### Disable a Specific Diagram Type

To disable support for a specific diagram type, such as ChartJS:

```php
$ParsedownExtended->setSetting('diagrams', [
    'chartjs' => false
]);
```