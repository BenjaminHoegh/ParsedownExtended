---
title: Alerts
---

# Alerts

## Description

Alerts in Markdown provide a way to emphasize important information, such as tips, warnings, or critical notes. ParsedownExtended enhances Markdown with a customizable alerts feature that allows you to easily integrate these types of messages into your documents. The alert types are configurable, allowing you to define the specific categories of alerts that suit your content needs.

## Configuration Syntax

Configure alert processing in ParsedownExtended using the `config()->set()` method:

```php
$ParsedownExtended->config()->set('alerts', (bool|array) $value);
```

This setting can be a simple boolean to enable or disable all alerts, or an array to manage specific alert behaviors with greater precision.

## Parameters

- **types** (array): Specifies the types of alerts available. The default types are `note`, `tip`, `important`, `warning`, and `caution`.

## Examples

### Disable GFM Alerts

To disable all alert processing in your Markdown:

```php
$ParsedownExtended->config()->set('alerts', false);
```

### Customize Alert Types

To customize the types of alerts available in your Markdown:

```php
$ParsedownExtended->config()->set('alerts', [
    'types' => ['note', 'warning', 'custom-type']
]);
```

In this example, only `note`, `warning`, and a custom alert type `custom-type` will be recognized and rendered as alerts.
