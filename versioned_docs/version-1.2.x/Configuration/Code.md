---
title: Code
---

# Code

## Description

Code snippets within Markdown documents can be presented in two distinct styles: inline and block. Inline code is used for highlighting code or commands within a sentence, whereas block code is suited for larger code excerpts or examples that should stand apart from the main text. ParsedownExtended enhances the management of both inline and block code snippets, offering configurable settings to fine-tune their processing and presentation.

## Configuration Syntax

To configure code through the `setSetting` method processing:

```php
$ParsedownExtended->setSetting('code', (boolean|array) $value);
```

- `$value` is a boolean indicating whether inline code processing is enabled (`true`) or disabled (`false`). The default setting usually enables inline code formatting.

## Examples

### Disabling All Code Processing

To disable the processing of all code, including inline and block code:

```php
$ParsedownExtended->setSetting('code', false);
```

### Disabling Inline Code

To disable the formatting of inline code, preventing text surrounded by backticks from being rendered distinctly:

```php
$ParsedownExtended->setSetting('code.inline', false);
```

### Disabling Block Code

To disable the processing of block code, which is usually delimited by triple backticks or indentation:

```php
$ParsedownExtended->setSetting('code.block', false);
```