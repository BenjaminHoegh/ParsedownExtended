---
layout: default
title: Lists
has_children: true
---

# Lists

## Syntax
```php
"lists" => (boolean|array) $value // default true
```

## Description
Lists can be organized into either ordered, unordered lists and [task](/docs/lists/task%20list.html). And both types of lists can also be nested.

<div class="code-example" markdown="1">
<ul>
  <li>First ordered list item.</li>
  <li>Second ordered list item.</li>
  <li>Third ordered list item.</li>
  <li>Fourth ordered list item.</li>
  <li>Fifth ordered list item.</li>
</ul>
</div>
```
- First ordered list item.
- Second ordered list item.
- Third ordered list item.
- Fourth ordered list item.
- Fifth ordered list item.
```


## Examples

### Disable
Disable lists

```php
$Parsedown = new ParsedownExtended([
    'lists' => false
]);
```
