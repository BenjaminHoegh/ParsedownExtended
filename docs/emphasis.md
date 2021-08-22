---
layout: default
title: Emphasis
---

# Emphasis

## Syntax
```php
"emphasis" => (boolean) $value // default true
```

## Description
When writing your content in Markdown, you might want to place a bit more emphasis on certain words or phrases. you can emphasize your text in either italics, bold, or both.

## Examples

### Disable
Disable emphasis

```php
$Parsedown = new ParsedownExtended([
    "emphasis" => false
]);
```
