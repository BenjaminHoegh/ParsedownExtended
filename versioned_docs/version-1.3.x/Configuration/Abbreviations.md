---
title: Abbreviations
---

# Abbreviations

## Description

The Abbreviations feature provides automatic detection and formatting of abbreviations within your Markdown content. By wrapping abbreviations in `<abbr>` tags, it enhances text comprehension and accessibility, allowing users to see the full meaning of abbreviations on hover.

## Configuration Syntax

To configure the Abbreviations feature, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for the Abbreviations feature:

```php
$configValue = config()->get('abbreviations');
```

### Setting the Configuration

To adjust the Abbreviations feature, use:

```php
config()->set('abbreviations', (bool|array) $value);
```

- `$value` can be a boolean to enable or disable the feature globally, or an array for more detailed configuration options.

## Configuration Options

The Abbreviations feature supports the following settings:

- **allow_custom** (bool): Allows the definition of custom abbreviations directly within your Markdown content. This option is enabled by default.
- **predefined** (array): Provides a list of predefined abbreviations and their full meanings to ensure consistency across your documents.

## Examples

### Disable Abbreviations

To completely disable the processing of abbreviations:

```php
$ParsedownExtended->config()->set('abbreviations', false);
```

### Predefine Abbreviations

To set up a predefined list of abbreviations:

```php
$ParsedownExtended->config()->set('abbreviations', [
    'predefined' => [
        'CSS' => 'Cascading Style Sheets',
        'HTML' => 'HyperText Markup Language',
        'JS' => 'JavaScript',
    ],
]);
```

### Use Predefined Abbreviations Only

To restrict usage to only predefined abbreviations and disable custom abbreviations:

```php
$ParsedownExtended->config()->set('abbreviations', [
    'allow_custom' => false,
    'predefined' => [
        'CSS' => 'Cascading Style Sheets',
        'HTML' => 'HyperText Markup Language',
        'JS' => 'JavaScript',
    ],
]);
```