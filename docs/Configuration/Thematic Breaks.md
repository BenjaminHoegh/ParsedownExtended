---
title: Thematic Breaks
---

# Thematic Breaks

## Description

Thematic breaks, also known as horizontal rules, are a handy element in Markdown for visually dividing sections of text in your documents. A thematic break is represented by three or more consecutive hyphens (`---`), asterisks (`***`), or underscores (`___`). Regardless of the symbol used, thematic breaks produce the same visual output.

## Configuration Syntax

Control the rendering of thematic breaks in ParsedownExtended with the `config()->set()` method:

```php
$ParsedownExtended->config()->set('thematic_breaks', (bool) $value);
```

This setting accepts a boolean value to enable or disable thematic breaks globally.

## Examples

### Enable Thematic Breaks

To enable thematic breaks with default settings:

```php
$ParsedownExtended->config()->set('thematic_breaks', true);
```

### Disable Thematic Breaks

To completely disable thematic breaks:

```php
$ParsedownExtended->config()->set('thematic_breaks', false);
```

This documentation provides users with clear guidance on how to configure the rendering of thematic breaks within their Markdown documents using ParsedownExtended, with the appropriate syntax and context.