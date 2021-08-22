---
layout: default
title: Inline Code
parent: Inline code and blocks
---

# Inline Code

## Syntax
```php
"inline" => (boolean) $value // default true
```

## Description
Used to reference snippets of code as examples. This is particularly common in technical documentation. Markdown allows you to format code snippets using backticks (`) that wrap your code snippet

## Examples

### Disable
Disable inline code

```php
$Parsedown = new ParsedownExtended([
    'code' => {
        'inline' => false
    }
]);
```
