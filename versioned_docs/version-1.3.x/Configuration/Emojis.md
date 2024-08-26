---
title: Emojis
---

# Emojis

## Description

ParsedownExtended enriches Markdown documents with the capability to insert emojis using shortcodes, a feature that enhances readability and emotional expression within text. Shortcodes, which are emoji names enclosed in colons (e.g., `:smile:`), are automatically converted into their corresponding emoji characters, allowing for a more engaging and visually appealing document.

## Configuration Syntax

To enable or disable emoji shortcodes in ParsedownExtended, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for emoji shortcodes:

```php
$configValue = $ParsedownExtended->config()->get('emojis');
```

### Setting the Configuration

To adjust the emoji shortcode processing:

```php
$ParsedownExtended->config()->set('emojis', (bool) $value);
```

- `$value` is a boolean indicating whether emoji shortcodes should be processed (`true`) or not (`false`).

## Examples

### Enable Emoji Shortcodes

To enable the interpretation and conversion of emoji shortcodes into actual emojis:

```php
$ParsedownExtended->config()->set('emojis', true);
```

### Usage Example

Incorporate emojis into your Markdown by using shortcodes:

**Markdown Input:**

```markdown
Gone camping! :tent: Be back soon.

That is so funny! :joy:
```

**Rendered Output:**

Gone camping! ⛺ Be back soon.

That is so funny! 😂

This setup allows you to easily control whether emojis are converted from shortcodes within your Markdown content, helping you create more expressive and visually appealing documents.