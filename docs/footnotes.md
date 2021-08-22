---
layout: default
title: Footnotes
---

# Footnotes <label class="label label-gray">Extra Required</label>

## Syntax

```php
"footnotes" => (boolean) $value // default true
```
## Description

Footnotes allow you to add notes and references without cluttering the body of the document. When you create a footnote, a superscript number with a link appears where you added the footnote reference. Readers can click the link to jump to the content of the footnote at the bottom of the page.

## Examples

### Disable
Disable emphasis

```php
$Parsedown = new ParsedownExtended([
    "footnotes" => false
]);
```
