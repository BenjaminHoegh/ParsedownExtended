---
layout: default
title: Headings
has_toc: false
---

# Headings

## Syntax

```php
"headings" => (boolean|array) $value // default true
```

## Description
headings are formatted with hashes (#) in front of the line containing your heading. You can use up to six hashes, with the number of hashes corresponding to a heading level.

## Parameters

If `$value` is a array, then `headings` will be `true` by default.

- **auto_anchors** (boolean)  
  To enable/disable automatic heading permalink.
- **allowed** (array)  
  Choose what headings level can be used in the markdown.
- **blacklist** (array)  
  Blacklist any ID's from being used as a anchors.


## Examples

### Disable
Disable headings
```php
$Parsedown = new ParsedownExtended([
    "headings" => true
]);
```


### Heading permalink
To enable/disable automatic heading permalink use the following:
```php
$Parsedown = new ParsedownExtended([
    "headings" => [
        "auto_anchors" => true
    ]
]);
```

### Allowed
Choose what headings level can be used in the markdown
```php
$Parsedown = new ParsedownExtended([
    "headings" => [
        "allowed" => ["h1","h2","h3"]
    ]
]);
```


### Blacklist
Using blacklists.
```php
$Parsedown = new ParsedownExtended([
    "headings" => [
        "blacklist" => ["my_blacklisted_header_id","another_blacklisted_id"]
    ]
]);
```
