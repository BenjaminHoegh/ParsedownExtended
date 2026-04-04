---
title: Links
---

# Links

## Description

In Markdown, links are crucial for directing readers to additional resources or related content. ParsedownExtended enhances link functionality in Markdown, offering options to customize how links are processed, including the handling of email addresses as mailto links. This feature allows for greater control over link creation and presentation in your documents.

## Configuration Syntax

Configure link processing in ParsedownExtended using the `config()->set()` method:

```php
$ParsedownExtended->config()->set('links', (bool|array) $value);
```

This setting can be a simple boolean to enable or disable all link processing, or an array for more granular control over specific types of links.

## Parameters

- **email_links** (boolean): Determines whether email addresses are automatically converted into mailto links. This is enabled by default.
- **external_links** (boolean|array): Controls how external links are handled. When enabled, external links can be automatically annotated with security-related attributes.
    - **nofollow** (boolean): Adds `rel="nofollow"` to external links. Enabled by default.
    - **noopener** (boolean): Adds `rel="noopener"` to external links. Enabled by default.
    - **noreferrer** (boolean): Adds `rel="noreferrer"` to external links. Enabled by default.
    - **open_in_new_window** (boolean): Adds `target="_blank"` to external links. Enabled by default.
    - **internal_hosts** (array): A list of hostnames to treat as internal (not external). Links to these hosts will not receive external-link attributes.

## Examples

### Disable All Link Processing

To disable the processing and rendering of all links:

```php
$ParsedownExtended->config()->set('links.email_links', false);
```

### Disable External Link Attributes

To allow external links without any extra security attributes:

```php
$ParsedownExtended->config()->set('links.external_links', false);
```

### Customize External Link Attributes

To selectively enable only specific attributes on external links:

```php
$ParsedownExtended->config()->set('links.external_links.nofollow', true);
$ParsedownExtended->config()->set('links.external_links.noopener', true);
$ParsedownExtended->config()->set('links.external_links.noreferrer', false);
$ParsedownExtended->config()->set('links.external_links.open_in_new_window', false);
```

### Mark Hosts as Internal

To prevent certain domains from being treated as external links:

```php
$ParsedownExtended->config()->set('links.external_links.internal_hosts', [
    'mysite.com',
    'cdn.mysite.com',
]);
```