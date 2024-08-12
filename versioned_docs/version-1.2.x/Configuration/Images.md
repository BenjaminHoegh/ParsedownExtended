---
title: Images
---

# Images

## Description

Incorporating images into Markdown greatly enhances the visual impact and effectiveness of your content. ParsedownExtended supports the standard Markdown syntax for embedding images, making it simple to include visuals in your documents. With this feature, you can easily add images using a syntax similar to Markdown links, enabling a more engaging and illustrative content presentation.

## Configuration Syntax

Control the processing of images in Markdown using ParsedownExtended's `setSetting` method:

```php
$ParsedownExtended->setSetting('images', (boolean) $value);
```

This setting allows you to enable (`true`) or disable (`false`) the rendering of images in your Markdown content.

## Examples

### Enable Images

Images are enabled by default. To explicitly ensure they are enabled:

```php
$ParsedownExtended->setSetting('images', true);
```

### Disable Images

To prevent Markdown syntax from being processed as images, effectively disabling image rendering:

```php
$ParsedownExtended->setSetting('images', false);
```