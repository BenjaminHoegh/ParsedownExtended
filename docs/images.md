---
layout: default
title: Images
---

# Images

## Syntax
```php
"images" => (boolean) $value // default true
```

## Description
A picture is worth a thousand words, as they say. Inserting an image into your Markdown file is similar to the formatting for links.

## Examples

### Disable
Disable images

```php
$Parsedown = new ParsedownExtended([
    "images" => false
]);
```
