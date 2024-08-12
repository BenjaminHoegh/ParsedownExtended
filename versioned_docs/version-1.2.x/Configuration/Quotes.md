---
title: Quotes
description: Configure the processing of blockquotes in ParsedownExtended
---

# Quotes

## Description

Blockquotes allow for the inclusion of quoted sections from external sources within your Markdown documents. This capability, provided by the original Parsedown parser, is enhanced in ParsedownExtended by offering configurable settings to manage how blockquotes are processed. By prefixing text with a `>` character, users can create blockquotes, which are then automatically formatted with `<blockquote>` tags by Parsedown.

## Configuration Syntax

Configure the processing of blockquotes using ParsedownExtended's `setSetting` method:

```php
$ParsedownExtended->setSetting('quotes', (boolean) $value);
```

- `$value` is a boolean that enables (`true`) or disables (`false`) the processing of blockquotes.

## Examples

### Disable Blockquotes

To disable the automatic conversion of text preceded by `>` into blockquotes:

```php
$ParsedownExtended->setSetting('quotes', false);
```