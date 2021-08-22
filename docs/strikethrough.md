---
layout: default
title: Strikethrough
---

# Strikethrough

## Syntax
```php
"strikethrough" => (boolean) $value // default true
```

## Description
You can strikethrough words by putting a horizontal line through the center of them. The result looks ~~like this~~. This feature allows you to indicate that certain words are a mistake not meant for inclusion in the document. To strikethrough words, use two tilde symbols (~~) before and after the words.

## Examples

### Disable
Disable Strikethrough

```php
$Parsedown = new ParsedownExtended([
    "strikethrough" => false
]);
```
