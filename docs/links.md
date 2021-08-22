---
layout: default
title: Links
---

# Links

## Syntax
```php
"links" => (boolean|array) $value // default true
```

## Description
Description

## Parameters

If `$value` is a array, then `links` will be `true` by default.

- **email_links** (boolean)  
  The ability to convert `<my@email.com>` into a mailto link, this is on by default.

## Examples

### Disable
Disable links

```php
$Parsedown = new ParsedownExtended([
    "links" => false
]);
```

### Disable Emails
Disable mailto links

```php
$Parsedown = new ParsedownExtended([
    "links" => [
        "email_links" => false
    ]
]);
```
