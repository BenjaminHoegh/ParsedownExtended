---
layout: default
title: Code snippets
parent: Code blocks and snippets
---

# Code snippets

## Syntax
```php
"snippets" => (boolean) $value // default true
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
