---
layout: default
title: Definition Lists
---

# Definition list <label class="label label-gray">Extra Required</label>

## Syntax

```php
"definition_list" => (boolean) $value // default true
```

## Description
Allows you to create definition lists of terms and their corresponding definitions.


## Examples

### Disable
Disable definition lists:
```php
$Parsedown = new ParsedownExtended([
    "definition_list" => false
]);
```

