---
title: Definition Lists
---

# Definition Lists

## Description

Definition lists allow for the organization of terms and their corresponding definitions in a structured format. This functionality enables authors to create glossaries or specify key concepts with descriptions directly within their Markdown content. While the capability to interpret definition lists syntax comes from Parsedown, ParsedownExtended provides enhanced support by allowing users to easily enable or disable this feature through its configuration settings.

## Configuration Syntax

To configure the processing of definition lists in your Markdown with ParsedownExtended, use the `setSetting` method:

```php
$ParsedownExtended->setSetting('definition_lists', (boolean) $value);
```

- `$value` is a boolean indicating whether definition lists should be processed (`true`) or ignored (`false`).

## Examples

### Disable Definition Lists

To prevent the automatic processing of definition lists, thereby disabling the feature:

```php
$ParsedownExtended->setSetting('definition_lists', false);
```