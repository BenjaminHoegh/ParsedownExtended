---
title: Images
---

# Images

## Description

Incorporating images into Markdown greatly enhances the visual impact and effectiveness of your content. ParsedownExtended supports the standard Markdown syntax for embedding images, making it simple to include visuals in your documents. With this feature, you can easily add images using a syntax similar to Markdown links, enabling a more engaging and illustrative content presentation.

## Configuration Syntax

Control the processing of images in Markdown using ParsedownExtended's `config()->set()` and `config()->get()` methods:

### Getting the Current Configuration

To retrieve the current configuration for image processing:

```php
$configValue = config()->get('images');
```

### Setting the Configuration

To enable or disable the rendering of images in your Markdown content:

```php
config()->set('images', (bool) $value);
```

This setting allows you to enable (`true`) or disable (`false`) the rendering of images in your Markdown content.

## Examples

### Enable Images

Images are enabled by default. To explicitly ensure they are enabled:

```php
$ParsedownExtended->config()->set('images', true);
```

### Disable Images

To prevent Markdown syntax from being processed as images, effectively disabling image rendering:

```php
$ParsedownExtended->config()->set('images', false);
```

This configuration provides you with the flexibility to control whether images are included in your Markdown documents, ensuring that your content is presented exactly as you intend.