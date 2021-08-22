---
layout: default
title: Blockqoutes
---

# Blockqoutes

## Syntax

```php
"qoutes" => (boolean) $value // default true
```

## Description
Used when you want to reference an external source using quotation marks. You represent any blockquote by preceding the first line of the block quote with a greater-than sign or angle bracket (>). Any defined abbreviation is wrapped in an `<blockquote>` tag.

## Examples

### Disable
Disable blockqoutes:
```php
$Parsedown = new ParsedownExtended([
    "qoutes" => false
]);
```
