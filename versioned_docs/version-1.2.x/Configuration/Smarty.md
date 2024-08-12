---
title: Smarty
---

# Smartypants

## Description

Smartypants in ParsedownExtended automatically transforms basic ASCII punctuation marks into their typographically correct HTML entities. This feature enhances the readability and aesthetic appeal of your text by converting straight quotes to curly quotes, dashes to en-dashes and em-dashes, and ellipses to their HTML equivalents. It's particularly useful for ensuring that your Markdown content maintains high typographical standards without manual adjustments.

## ASCII to HTML Entity Conversion

| ASCII symbol | Replacements    | HTML Entities       | Substitution Keys                  |
| ------------ | --------------- | ------------------- | ---------------------------------- |
| `''`         | &lsquo; &rsquo; | `&lsquo;` `&rsquo;` | `"left-single-quote"`, `"right-single-quote"` |
| `""`         | &ldquo; &rdquo; | `&ldquo;` `&rdquo;` | `"left-double-quote"`, `"right-double-quote"` |
| `<< >>`      | &laquo; &raquo; | `&laquo;` `&raquo;` | `"left-angle-quote"`, `"right-angle-quote"`   |
| `...`        | &hellip;        | `&hellip;`          | `"ellipsis"`                        |
| `--`         | &ndash;         | `&ndash;`           | `"ndash"`                           |
| `---`        | &mdash;         | `&mdash;`           | `"mdash"`                           |

## Configuration Syntax

Configure Smartypants using the `setSetting` method:

```php
$ParsedownExtended->setSetting('smarty', (boolean|array) $value);
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
$ParsedownExtended->setSetting('smarty', [
    'substitutions' => [
        'left-single-quote' => '&sbquo;', // Single bottom quote
        'right-single-quote' => '&lsquo;', // Single top quote
        'left-double-quote' => '&bdquo;', // Double bottom quote
        'right-double-quote' => '&ldquo;' // Double top quote
    ]
]);
```

### Enable Smartypants

To enable Smartypants and automatically apply typographical enhancements:

```php
$ParsedownExtended->setSetting('smarty', true);
```