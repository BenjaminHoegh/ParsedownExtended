---
title: Headings
---

# Headings

## Description

Markdown headings, marked by hash characters (`#`), indicate different levels of content hierarchy from `h1` to `h6`. ParsedownExtended not only supports these standard headings but also offers enhanced functionalities such as automatic permalink generation and customizable heading levels. A key feature is the ability for users to define their own logic for creating anchor IDs using the `setCreateAnchorIDCallback` method.

## Configuration Syntax

Configure headings using the `setSetting` method:

```php
$ParsedownExtended->setSetting('headings', (boolean|array) $value);
```

This setting can be a boolean to globally enable/disable headings or an array for more detailed configurations.

## Parameters

- **allowed** (array): Specify which heading levels are allowed.
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
$ParsedownExtended->setSetting('headings', false);
```

### Custom Anchor IDs

Implement custom logic for anchor IDs:

```php
$ParsedownExtended->setCreateAnchorIDCallback(function($text, $level) {
    return 'custom-anchor-' . $level . '-' . strtolower(str_replace(' ', '-', $text));
});
```

### Configure Allowed Headings

Specify allowed heading levels:

```php
$ParsedownExtended->setSetting('headings.allowed', ['h1', 'h2', 'h3']);
```

### Blacklist Heading IDs

Define a blacklist for heading IDs:

```php
$ParsedownExtended->setSetting('headings.auto_anchors.blacklist', ['my_blacklisted_header_id', 'another_blacklisted_id']);
```