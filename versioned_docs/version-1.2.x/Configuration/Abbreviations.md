---
title: Abbreviations
---

# Abbreviations

## Description

The Abbreviations feature enables the automatic detection and formatting of abbreviations within Markdown content. By wrapping abbreviations in `<abbr>` tags, it enhances text comprehension and accessibility, providing full meanings on hover.

## Configuration Syntax

To adjust the abbreviations feature, utilize the `setSetting` method:

```php
$ParsedownExtended->setSetting('abbreviations', (boolean|array) $value);
```

- `$value` can be a boolean to enable/disable this feature globally or an array for detailed configurations.

## Parameters

This feature allows the following configurations:

- **allow_custom_abbr** (boolean): Permit the definition of custom abbreviations directly within your Markdown. Enabled by default.
- **predefine** (array): Define a list of abbreviations with their full meanings to ensure consistency across your documents.

## Examples

### Disable Abbreviations

To disable abbreviation processing entirely:

```php
$ParsedownExtended->setSetting('abbreviations', false);
```

### Predefine Abbreviations

To establish a predefined set of abbreviations:

```php
$ParsedownExtended->setSetting('abbreviations', [
    'predefine' => [
        'CSS' => 'Cascading Style Sheets',
        'HTML' => 'HyperText Markup Language',
        'JS' => 'JavaScript',
    ],
]);
```

### Custom Abbreviations Only

To use only predefined abbreviations and disable custom ones:

```php
$ParsedownExtended->setSetting('abbreviations', [
    'allow_custom_abbr' => false,
    'predefine' => [
        'CSS' => 'Cascading Style Sheets',
        'HTML' => 'HyperText Markup Language',
        'JS' => 'JavaScript',
    ],
]);
```