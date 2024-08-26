---
title: Links
---

# Links

## Description

In Markdown, links are essential for guiding readers to additional resources or related content. ParsedownExtended enhances link functionality by providing advanced options for customizing how links are handled. This includes detailed settings for external links, the automatic conversion of email addresses to mailto links, and security enhancements for link behavior. These features give you greater control over how links are processed and displayed in your documents.

## Configuration Syntax

Configure link processing in ParsedownExtended using the `config()->set()` method:

```php
$ParsedownExtended->config()->set('links', (bool|array) $value);
```

This setting can be a simple boolean to enable or disable all link processing, or an array to manage specific link behaviors with greater precision.

## Parameters

- **email_links** (boolean): Determines whether email addresses are automatically converted into mailto links. Enabled by default.
- **external_links** (array): Allows you to configure specific behaviors for external links. The available options under `external_links` are:
  - **nofollow** (boolean): Adds `nofollow` to external links to improve SEO management. Enabled by default.
  - **noopener** (boolean): Adds `noopener` to external links to enhance security. Enabled by default.
  - **noreferrer** (boolean): Ensures no referrer information is passed when opening external links. Enabled by default.
  - **open_in_new_window** (boolean): Opens external links in a new window by default. Enabled by default.
  - **internal_hosts** (array): Specifies internal hosts that should be excluded from external link settings. Defaults to an empty array.

## Examples

### Disable All Link Processing

To disable the processing and rendering of all links:

```php
$ParsedownExtended->config()->set('links', false);
```

### Disable Mailto Links

To disable the automatic conversion of email addresses into mailto links:

```php
$ParsedownExtended->config()->set('links', [
    'email_links' => false
]);
```

### Customize External Link Behavior

To configure external links with specific settings:

```php
$ParsedownExtended->config()->set('links', [
    'external_links' => [
        'nofollow' => true,
        'noopener' => true,
        'noreferrer' => true,
        'open_in_new_window' => true,
        'internal_hosts' => ['yourdomain.com']
    ]
]);
```

### Exclude Certain Hosts from External Link Settings

To exclude specific internal hosts from being treated as external links:

```php
$ParsedownExtended->config()->set('links', [
    'external_links' => [
        'internal_hosts' => ['yourdomain.com', 'anotherdomain.com']
    ]
]);
```

These examples demonstrate how to apply the new link configuration settings in `ParsedownExtended`, allowing users to tailor link processing to their needs with ease and precision.