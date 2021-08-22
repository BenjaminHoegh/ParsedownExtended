---
layout: default
title: Smartypants
---

# Smartypants

## Syntax
```php
"smarty" => (boolean|array) $value // default true
```

## Description
Converts ASCII dashes, quotes and ellipses to
their HTML entity equivalents.

ASCII symbol | Replacements    | HTML Entities       | Substitution Keys
------------ | --------------- | ------------------- | -----------------
`''`         | &lsquo; &rsquo; | `&lsquo;` `&rsquo;` | `"left-single-quote"`, `"right-single-quote"` 
`""`         | &ldquo; &rdquo; | `&ldquo;` `&rdquo;` | `"left-double-quote"`, `"right-double-quote"`
`<< >>`      | &laquo; &raquo; | `&laquo;` `&raquo;` | `"left-angle-quote"`, `"right-angle-quote"`
`...`        | &hellip;        | `&hellip;`          | `"ellipsis"`
`--`         | &ndash;         | `&ndash;`           | `"ndash"`
`---`        | &mdash;         | `&mdash;`           | `"mdash"`

## Parameters

If `$value` is a array, then `smarty` will be `true` by default.

- **smart_dashes** (boolean)  
  Whether to convert dashes.
- **smart_quotes** (boolean)   
  Whether to convert straight quotes.
- **smart_angled_quotes** (boolean)  
  Whether to convert angled quotes.
- **smart_ellipses** (boolean)  
  Whether to convert ellipses.
- **smart_shortcodes** (boolean)  
  Whether to convert shortcodes.
- **substitutions** (array)  
  Overwrite default substitutions.


## Examples

### Substitutions
Using the configuration option `substitutions` you can overwrite the
default substitutions. Just pass a dict mapping (a subset of) the
keys to the substitution strings.

For example, one might use the following configuration to get correct quotes for
the German language:

```php
{
    "smarty": {
        "substitutions": {
            "left-single-quote": "&sbquo;", # sb is not a typo!
            "right-single-quote": "&lsquo;",
            "left-double-quote": "&bdquo;",
            "right-double-quote": "&ldquo;"
        }
    }
}
```

### Enable
Enable smartypants

```php
$Parsedown = new ParsedownExtended([
    'smarty' => true
]);
```
