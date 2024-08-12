---
title: Headings
---

# Headings

## Description

Markdown headings, marked by hash characters (`#`), indicate different levels of content hierarchy from `h1` to `h6`. ParsedownExtended not only supports these standard headings but also offers enhanced functionalities such as automatic permalink generation and customizable heading levels. A key feature is the ability for users to define their own logic for creating anchor IDs using the `setCreateAnchorIDCallback` method.

## Configuration Syntax

To configure headings in ParsedownExtended, use the `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for headings:

```php
$configValue = $ParsedownExtended->config()->get('headings');
```

### Setting the Configuration

To configure the headings feature:

```php
$ParsedownExtended->config()->set('headings', (bool|array) $value);
```

This setting can be a boolean to globally enable or disable headings, or an array for more detailed configurations.

## Configuration Options

- **allowed_levels** (array): Specify which heading levels are allowed.
- **auto_anchors** (boolean): Toggle automatic permalink generation for headings.
    - **blacklist** (array): List of IDs to exclude from automatic anchor generation.
    - **delimiter** (string): Character(s) to use for separating words in anchor IDs.
    - **lowercase** (boolean): Whether to convert anchor IDs to lowercase.
    - **transliterate** (boolean): Whether to transliterate characters in anchor IDs.
    - **replacements** (array): List of characters to replace in anchor IDs.

## Examples

### Disable Headings

To disable headings processing:

```php
$ParsedownExtended->config()->set('headings', false);
```

### Custom Anchor IDs

Implement custom logic for anchor IDs:

```php
$ParsedownExtended->config()->set('headings.custom_anchor_id_callback', function($text, $level) {
    return 'custom-anchor-' . $level . '-' . strtolower(str_replace(' ', '-', $text));
});
```

### Configure Allowed Headings

Specify allowed heading levels:

```php
$ParsedownExtended->config()->set('headings.allowed_levels', ['h1', 'h2', 'h3']);
```

### Blacklist Heading IDs

Define a blacklist for heading IDs:

```php
$ParsedownExtended->config()->set('headings.auto_anchors.blacklist', ['my_blacklisted_header_id', 'another_blacklisted_id']);
```

This configuration gives you complete control over how headings are processed and rendered in your Markdown documents, allowing you to tailor the content hierarchy and linking behavior to your specific needs.