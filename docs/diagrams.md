---
layout: default
title: Diagrams
---

# Diagrams

## Syntax
```php
"diagrams" => (boolean) $value // default false
```

## Description
ParsedownExtended adds the ability to use diagrams in your markdown, by adding support for [ChartJS](https://www.chartjs.org) and [Mermaid](https://mermaid-js.github.io/mermaid/). By looking out for code related to diagrams and avoid it so it doesn"t get manipulated. To use the function you will have to include their .js files in your project.

## Examples

### Enable
Enable diagrams

```php
$Parsedown = new ParsedownExtended([
    "diagrams" => true
]);
```
