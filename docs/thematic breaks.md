---
layout: default
title: Thematic Breaks
---

# Thematic Breaks

## Syntax
```php
"thematic_breaks" => (boolean) $value // default true
```

## Description
A thematic breaks is a useful little element that you can use to visually split up blocks of text within your Markdown file. A thematic breaks is represented by three or more hyphens (-), asterisks (*), or underscores (_). Whichever symbol you use renders the same output.

## Examples

### Disable
Disable thematic breaks

```php
$Parsedown = new ParsedownExtended([
    "thematic_breaks" => false
]);
```
