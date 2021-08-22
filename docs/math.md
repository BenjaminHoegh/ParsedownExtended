---
layout: default
title: Math
---

# Math

## Syntax

```php
"math" => (boolean|array) $value // default false
```

## Description
ParsedownExtended adds the ability to use LaTeX in your markdown, by using regular expression to find and recognize LaTeX to avoid formatting it. This enables you to use a library like [KaTeX](https://katex.org) to make the on-device rendering of the code.

## Eamples

### Enable
To enable LaTeX support:
```php
$Parsedown = new ParsedownExtended([
    "math" => true
]);
```

### Single Dollar Match
If you want to use single dollar to active LaTeX mode you can do the following:
```php
$Parsedown = new ParsedownExtended([
    "math" => [
        "single_dollar" => true
    ]
]);
```
