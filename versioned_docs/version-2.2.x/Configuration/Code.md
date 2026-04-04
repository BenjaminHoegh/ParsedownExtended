---
title: Code
---

# Code

## Description

Code snippets within Markdown documents can be presented in two distinct styles: inline and block. Inline code is used for highlighting code or commands within a sentence, whereas block code is suited for larger code excerpts or examples that should stand apart from the main text. ParsedownExtended enhances the management of both inline and block code snippets, offering configurable settings to fine-tune their processing and presentation.

## Configuration Syntax

To configure the code processing settings, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for code processing:

```php
$configValue = $ParsedownExtended->config()->get('code');
```

### Setting the Configuration

To adjust the code processing settings:

```php
$ParsedownExtended->config()->set('code', (bool|array) $value);
```

- `$value` is a boolean indicating whether inline code processing is enabled (`true`) or disabled (`false`). Alternatively, you can use an array for more detailed configuration options. By default, inline code formatting is usually enabled.

## Examples

### Disabling All Code Processing

To disable the processing of all code, including both inline and block code:

```php
$ParsedownExtended->config()->set('code', false);
```

### Disabling Inline Code

To disable the formatting of inline code, preventing text surrounded by backticks from being rendered distinctly:

```php
$ParsedownExtended->config()->set('code.inline', false);
```

### Disabling Block Code

To disable the processing of block code, which is usually delimited by triple backticks or indentation:

```php
$ParsedownExtended->config()->set('code.block', false);
```