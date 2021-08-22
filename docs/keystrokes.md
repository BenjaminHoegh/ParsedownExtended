---
layout: default
title: Keystrokes
---

# Keystrokes

## Syntax
```php
"keystrokes" => (boolean) $value // default true
```

## Description
A keystroke is the pressing of a single key in a physical or virtual keyboard or any other input device.

## Examples

### Disable
Disable keystrokes

```php
$Parsedown = new ParsedownExtended([
    "keystrokes" => false
]);
```
