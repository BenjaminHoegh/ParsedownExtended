---
layout: default
title: Table of Content
has_toc: false
---

# Table of Content

## Syntax
```php
"toc" => (boolean|array) $value // default false
```

## Description
Automatic create a ToC(Table of Content) there include every heading in the document, unless you don't want it to be included. You do not need to add anchors individually to every title. This is an automated process. 

## Parameters

If `$value` is a array, then `toc` will be `true` by default.

- **delimiter** (boolean)  
  Whether to convert dashes.
- **headings** (boolean)  
  Whether to convert dashes.
- **lowercase** (boolean)  
  Whether to convert dashes.
- **transliterate** (boolean)  
  Whether to convert dashes.
- **urlencode** (boolean)  
  Whether to convert dashes.
- **set_toc_tag** (string)  
  Sets user defined ToC markdown tag. Use this method before `text()` or `body()` method if you want to use the ToC tag rather than the "`[toc]`". Empty value sets the default ToC tag.

### Seperated ToC list
With the `contentsList()` method, you can get just the "ToC".
```php
$toc = $Parsedown->contentsList();
```

Returns the parsed content WITHOUT parsing `[toc]` tag.
```php
$body = $Parsedown->body();
```

Returns the parsed content and `[toc]` tag(s) parsed as well.
```php
$text = $Parsedown->text();
```

```php
// Parse body and ToC separately
$content = file_get_contents('sample.md');
$Parsedown = new \ParsedownToC();

$body = $Parsedown->body($content);
$toc = $Parsedown->contentsList();

echo $toc;  // Table of Contents in <ul> list
echo $body; // Main body
```

## Examples

### Enable
Enable ToC

```php
$Parsedown = new ParsedownExtended([
    'toc' => true
]);
```

### Delimiter
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'delimiter' => true 
    ]
]);
```
### Headings
Choose what headings level to include in the ToC list.
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'headings' => ['h1','h2','h3']  
    ]
]);
```
### Lowercase
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'lowercase' => true 
    ]
]);
```
### Transliterate
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'transliterate' => true 
    ]
]);
```
### Urlencode
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'urlencode' => true 
    ]
]);
```
