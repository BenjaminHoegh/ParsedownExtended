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

To enable the diagrams feature in ParsedownExtended, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for diagram support:

```php
$configValue = $ParsedownExtended->config()->get('diagrams');
```

### Setting the Configuration

To adjust the diagram processing settings:

```php
$ParsedownExtended->config()->set('diagrams', (bool|array) $value);
```

- `$value` can be a boolean to enable or disable diagram support globally, or an array for more specific configuration options.

## Configuration Options

This feature allows the following settings:

- **chartjs**: Enable or disable support for ChartJS diagrams.
- **mermaid**: Enable or disable support for Mermaid diagrams.

## Examples

### Enable Diagrams

To activate diagram support, ensuring that Markdown containing ChartJS or Mermaid syntax is properly recognized and left intact for client-side rendering:

```php
$ParsedownExtended->config()->set('diagrams', true);
```

### Disable a Specific Diagram Type

To disable support for a specific diagram type, such as ChartJS:

```php
$ParsedownExtended->config()->set('diagrams', [
    'chartjs' => false
]);
```

This configuration allows you to control how diagram syntax is handled within your Markdown content, ensuring that it is processed according to your project’s requirements.