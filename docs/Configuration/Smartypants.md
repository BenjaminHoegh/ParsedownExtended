---
title: Smartypants
---

# Smartypants

## Description

Smartypants in ParsedownExtended automatically transforms basic ASCII punctuation marks into their typographically correct HTML entities. This feature enhances the readability and aesthetic appeal of your text by converting straight quotes to curly quotes, dashes to en-dashes and em-dashes, and ellipses to their HTML equivalents. It's particularly useful for ensuring that your Markdown content maintains high typographical standards without manual adjustments.

## ASCII to HTML Entity Conversion

| ASCII symbol | Replacements    | HTML Entities       | Substitution Keys                  |
| ------------ | --------------- | ------------------- | ---------------------------------- |
| `''`         | &lsquo; &rsquo; | `&lsquo;` `&rsquo;` | `"left_single_quote"`, `"right_single_quote"` |
| `""`         | &ldquo; &rdquo; | `&ldquo;` `&rdquo;` | `"left_double_quote"`, `"right_double_quote"` |
| `<< >>`      | &laquo; &raquo; | `&laquo;` `&raquo;` | `"left_angle_quote"`, `"right_angle_quote"`   |
| `...`        | &hellip;        | `&hellip;`          | `"ellipsis"`                        |
| `--`         | &ndash;         | `&ndash;`           | `"ndash"`                           |
| `---`        | &mdash;         | `&mdash;`           | `"mdash"`                           |

## Configuration Syntax

Configure Smartypants using the `config()->set()` method:

```php
$ParsedownExtended->config()->set('smarty', (bool|array) $value);
```

## Parameters

Customize Smartypants with these options:

- **smart_dashes** (boolean): Convert dashes to en-dashes and em-dashes.
- **smart_quotes** (boolean): Convert straight quotes to curly quotes.
- **smart_angled_quotes** (boolean): Convert angled quotes.
- **smart_ellipses** (boolean): Convert triple periods to ellipses.
- **substitutions** (array): Overwrite default substitutions with custom mappings.

## Examples

### Custom Substitutions

To customize the substitutions for a specific language or style:

```php
$ParsedownExtended->config()->set('smarty', [
    'substitutions' => [
        'left_single_quote' => '&sbquo;', // Single bottom quote
        'right_single_quote' => '&lsquo;', // Single top quote
        'left_double_quote' => '&bdquo;', // Double bottom quote
        'right_double_quote' => '&ldquo;' // Double top quote
    ]
]);
```

### Enable Smartypants

To enable Smartypants and automatically apply typographical enhancements:

```php
$ParsedownExtended->config()->set('smarty', true);
```

This documentation ensures clarity and provides the full context for configuring Smartypants within ParsedownExtended, making it easy for users to apply these settings correctly in their projects.