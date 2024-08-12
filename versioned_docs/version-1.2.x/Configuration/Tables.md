---
title: Tables
---

# Tables

## Description

Tables are a key feature in Markdown for organizing and presenting data clearly and effectively. ParsedownExtended supports the standard Markdown syntax for creating tables, which involves using hyphens (`---`) for defining columns and pipes (`|`) for separating them. For enhanced readability and compatibility, it's recommended to include pipes at the beginning and end of each row as well.

## Configuration Syntax

Configure table rendering in ParsedownExtended using the `setSetting` method:

```php
$ParsedownExtended->setSetting('tables', (boolean|array) $value);
```

This setting can be a boolean to enable or disable table rendering globally, or an array for more specific configurations.

## Parameters

- **tablespan** (boolean): Enables or disables the use of table spans (`colspan` and `rowspan`) in tables. This feature is disabled by default.

## Examples

### Enable Table Rendering

To enable the rendering of tables with default settings:

```php
$ParsedownExtended->setSetting('tables', true);
```

### Enable Tablespan

To enable table spans, allowing for more complex table layouts:

```php
$ParsedownExtended->setSetting('tables', [
    'tablespan' => true
]);
```

### Disable Tables

To completely disable table rendering:

```php
$ParsedownExtended->setSetting('tables', false);
```

### Markdown Table Example

Create a table in Markdown:

```markdown
| Header 1 | Header 2 | Header 3 |
| -------- | -------- | -------- |
| Row 1    | Data     | Data     |
| Row 2    | Data     | Data     |
```