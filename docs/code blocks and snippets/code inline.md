---
layout: default
title: Inline Code
parent: Inline code and blocks
---

# Code snippets

## Syntax
```php
"inline" => (boolean) $value // default true
```

## Description
Used to reference snippets of code as examples. This is particularly common in technical documentation. Markdown allows you to format code snippets using backticks (`) that wrap your code snippet

## Examples

### Disable
Disable code snippets

```php
$Parsedown = new ParsedownExtended([
    'code' => {
        'snippets' => false
    }
]);
```
