---
title: Links
---

# Links

## Description

In Markdown, links are crucial for directing readers to additional resources or related content. ParsedownExtended enhances link functionality in Markdown, offering options to customize how links are processed, including the handling of email addresses as mailto links. This feature allows for greater control over link creation and presentation in your documents.

## Configuration Syntax

Configure link processing in ParsedownExtended using the `setSetting` method:

```php
$ParsedownExtended->setSetting('links', (boolean|array) $value);
```

This setting can be a simple boolean to enable or disable all link processing, or an array for more granular control over specific types of links.

## Parameters

- **email_links** (boolean): Determines whether email addresses are automatically converted into mailto links. This is enabled by default.

## Examples

### Disable All Link Processing

To disable the processing and rendering of all links:

```php
$ParsedownExtended->setSetting('links', false);
```

### Disable Mailto Links

To disable the automatic conversion of email addresses into mailto links:

```php
$ParsedownExtended->setSetting('links', [
    'email_links' => false
]);
```