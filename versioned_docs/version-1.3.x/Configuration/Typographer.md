---
title: Typographer
---

# Typographer

## Description

ParsedownExtended includes a typographer feature that enhances your Markdown writing experience. It provides useful shortcuts for common typographic symbols, making it easier and faster to create well-formatted content. Additionally, it offers limited misspelling detection and correction.

## Configuration Syntax

Configure the typographer feature in ParsedownExtended using the `config()->set()` method:

```php
$ParsedownExtended->config()->set('typographer', (bool) $value);
```

### Typographic Shortcodes:

- `(c)`: Replaced with &copy; (Copyright symbol).
- `(r)`: Replaced with &reg; (Registered trademark symbol).
- `(tm)`: Replaced with &trade; (Trademark symbol).
- `(p)`: Replaced with &para; (Paragraph symbol).
- `+-`: Replaced with &plusmn; (Plus-minus symbol).

### Misspelling Detection:

- `..` is replaced with `...` (Ellipsis).
- `.....` is replaced with `...` (Ellipsis).
- `?....` is replaced with `?..` (Question mark followed by ellipsis).
- `!....` is replaced with `!..` (Exclamation mark followed by ellipsis).

These typographic shortcuts and limited misspelling corrections can improve the quality and consistency of your Markdown content.

## Examples

### Enable Typographer

To enable the typographer feature:

```php
$ParsedownExtended->config()->set('typographer', true);
```

This documentation provides clear guidance on how to configure the typographer feature within ParsedownExtended, ensuring that users can easily enable and benefit from these typographic enhancements in their Markdown content.