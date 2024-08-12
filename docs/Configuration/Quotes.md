---
title: Quotes  
---

# Quotes

## Description

Blockquotes allow for the inclusion of quoted sections from external sources within your Markdown documents. This capability, provided by the original Parsedown parser, is enhanced in ParsedownExtended by offering configurable settings to manage how blockquotes are processed. By prefixing text with a `>` character, users can create blockquotes, which are then automatically formatted with `<blockquote>` tags by Parsedown.

## Configuration Syntax

Configure the processing of blockquotes using ParsedownExtended's `config()->set()` method:

```php
$ParsedownExtended->config()->set('quotes', (bool) $value);
```

- `$value` is a boolean that enables (`true`) or disables (`false`) the processing of blockquotes.

## Examples

### Disable Blockquotes

To disable the automatic conversion of text preceded by `>` into blockquotes:

```php
$ParsedownExtended->config()->set('quotes', false);
```

This documentation ensures that users have clear instructions on how to control the processing of blockquotes within their Markdown documents using ParsedownExtended, with full context provided.