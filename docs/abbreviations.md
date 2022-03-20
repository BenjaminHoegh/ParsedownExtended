---
layout: default
title: Abbreviations
---

# Abbreviation <label class="label label-gray">Extra Required</label>

## Syntax

```php
"abbreviations" => (boolean|array) $value // default true
```

## Description

Adds the ability to define abbreviations. Any defined abbreviation is wrapped in an `<abbr>` tag.


## Parameters

If `$value` is a array, then `abbreviations` will be `true` by default.

- **allow_custom_abbr** (boolean)
  The ability to define abbreviations, this is on by default.
- **predefine** (array)
  Used to predefine abbreviations.


## Examples


----

### Disable
Disable abbreviations:
```php
$Parsedown = new ParsedownExtended([
    "abbreviations" => false
]);
```

### Predefined
Predefine abbreviations:
```php
$Parsedown = new ParsedownExtended([
    "abbreviations" => [
        "predefine" => [
            "CSS" => "Cascading Style Sheet",
            "HTML" => "Hyper Text Markup Language",
            "JS" => "JavaScript"
        ]
    ]
]);
```

### Predefined only
Disable user/custom abbreviations by using `allow_custom_abbr`

```php
$Parsedown = new ParsedownExtended([
    "abbreviations" => [
        "allow_custom_abbr": false
        "predefine" => [
            "CSS" => "Cascading Style Sheet",
            "HTML" => "Hyper Text Markup Language",
            "JS" => "JavaScript"
        ]
    ]
]);
```