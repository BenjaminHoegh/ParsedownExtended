---
layout: default
title: Typographer
---

# Typographer

## Syntax
```php
"typographer" => (boolean) $value // default false
```

## Description
Adds some useful shortcodes to make typing faster, while also provide a very limited misspell helper

Shortcode    | Replacements    | HTML Entities       | Substitution Keys
------------ | --------------- | ------------------- | -----------------
`(c)`        | &copy;          | `&copy;`            | `"copy"`
`(r)`        | &reg;           | `&reg;`             | `"reg"`
`(tm)`       | &trade;         | `&trade;`           | `"trade"`
`(p)`        | &para;          | `&para;`            | `"para"`
`+-`         | &plusmn;        | `&plusmn;`          | `"plusmn"`


| Misspell   | Result   |
| ---------- | -------- | 
| ..         | ...      | 
| .....      | ...      | 
| ?....      | ?..      | 
| !....      | !..      | 


## Examples

### Enable
Enable typographer

```php
$Parsedown = new ParsedownExtended([
    'typographer' => true
]);
```
