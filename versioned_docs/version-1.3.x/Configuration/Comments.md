---
title: Comments
---

# Comments

## Description

The Comments feature in Markdown allows you to include notes or annotations within your content that won't be rendered in the final output. This is useful for adding explanations or reminders that are visible in the source but invisible to the end user. ParsedownExtended provides options to enable or disable the processing of comments within your Markdown files.

## Configuration Syntax

To configure the Comments feature, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for comment processing:

```php
$configValue = $ParsedownExtended->config()->get('comments');
```

### Setting the Configuration

To adjust the Comments feature:

```php
$ParsedownExtended->config()->set('comments', (bool) $value);
```

- `$value` is a boolean indicating whether comment processing is enabled (`true`) or disabled (`false`). By default, comment processing may be enabled or disabled based on your system's settings.

## Examples

### Disabling Comments

To disable the processing of comments in Markdown, preventing any comments from being included in the source:

```php
$ParsedownExtended->config()->set('comments', false);
```

### Enabling Comments

To enable the processing of comments, allowing annotations to be included in your Markdown files but not rendered in the final output:

```php
$ParsedownExtended->config()->set('comments', true);
```

This configuration will ensure that any comments added within the Markdown using the appropriate syntax will be processed accordingly, following your specified settings.