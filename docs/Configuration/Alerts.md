---
title: Alerts
---

# Alerts

## Description

ParsedownExtended supports GitHub Flavored Markdown (GFM) alert syntax. Alerts are block-quote-style callouts that are styled with a distinctive type label, making it easy to draw attention to notes, tips, warnings, and other important information.

```markdown
> [!NOTE]
> Highlights information that users should take into account.
> [!TIP]
> Optional information to help a user be more successful.
> [!IMPORTANT]
> Crucial information necessary for users to succeed.
> [!WARNING]
> Critical content demanding immediate user attention due to potential risks.
> [!CAUTION]
> Negative potential consequences of an action.
```

Each alert type is wrapped in a `<div>` with a CSS class composed of the base class and the lowercase alert type, e.g., `markdown-alert markdown-alert-note`.

## Configuration Syntax

To configure Alerts in ParsedownExtended, use the `config()->set()` method:

```php
$ParsedownExtended->config()->set('alerts', (bool|array) $value);
```

## Parameters

- **types** (array): The list of recognised alert type labels (case-insensitive). Defaults to `['note', 'tip', 'important', 'warning', 'caution']`.
- **class** (string): The base CSS class applied to the outer `<div>` wrapper of every alert. Defaults to `'markdown-alert'`.

## Examples

### Disable Alerts

To disable alert block processing entirely:

```php
$ParsedownExtended->config()->set('alerts', false);
```

### Custom Alert Types

To support only a subset of alert types, or to add your own:

```php
$ParsedownExtended->config()->set('alerts.types', ['note', 'warning', 'danger']);
```

### Custom CSS Class

To use a different base CSS class for styling:

```php
$ParsedownExtended->config()->set('alerts.class', 'callout');
```

With this setting a `> [!NOTE]` block would render as:
```html
<div class="callout callout-note">
  <p>...</p>
</div>
```

### Localised Alert Labels

You can replace the default English type labels with your own language or branding:

```php
$ParsedownExtended->config()->set('alerts.types', ['hinweis', 'tipp', 'warnung']);
```

Then use the matching labels in your Markdown:

```markdown
> [!HINWEIS]
> This is a note in German.
```