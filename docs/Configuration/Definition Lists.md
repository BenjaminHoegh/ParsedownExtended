---
title: Definition Lists
---

# Definition Lists

## Description

Definition lists allow for the organization of terms and their corresponding definitions in a structured format. This functionality enables authors to create glossaries or specify key concepts with descriptions directly within their Markdown content. While the capability to interpret definition list syntax comes from Parsedown, ParsedownExtended provides enhanced support by allowing users to easily enable or disable this feature through its configuration settings.

## Configuration Syntax

To configure the processing of definition lists in your Markdown, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for definition list processing:

```php
$configValue = $ParsedownExtended->config()->get('definition_lists');
```

### Setting the Configuration

To adjust the processing of definition lists:

```php
$ParsedownExtended->config()->set('definition_lists', (bool) $value);
```

- `$value` is a boolean indicating whether definition lists should be processed (`true`) or ignored (`false`).

## Examples

### Disable Definition Lists

To prevent the automatic processing of definition lists, thereby disabling the feature:

```php
$ParsedownExtended->config()->set('definition_lists', false);
```

This configuration allows you to control whether definition lists are recognized and formatted within your Markdown content based on your specific needs.