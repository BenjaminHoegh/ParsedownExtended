---
layout: default
title: Tables
---

# Tables

## Syntax
```php
"tables" => (boolean|array) $value // default true
```

## Description
To add a table, use three or more hyphens (---) to create each column’s header, and use pipes (|) to separate each column. For compatibility, you should also add a pipe on either end of the row.

## Parameters

If `$value` is a array, then `tables` will be `true` by default.

- **tablespan** (boolean)  
  Whether to allow tablespan, disabled by default.

## Examples

### Tablespan

<div class="code-example" markdown="1">
<table>
<thead>
<tr>
<th style="text-align: center;" colspan="3">Colspan</th>
<th colspan="2">for thead</th>
</tr>
</thead>
<tbody>
<tr>
<td rowspan="2">Lorem</td>
<td style="text-align: center;">ipsum</td>
<td style="text-align: right;">dolor</td>
<td>sit</td>
<td>amet</td>
</tr>
<tr>
<td style="text-align: center;">-</td>
<td style="text-align: right;" colspan="2">right align</td>
<td>.</td>
</tr>
<tr>
<td>,</td>
<td style="text-align: center;" colspan="2">center align</td>
<td colspan="2" rowspan="2">2x2 cell</td>
</tr>
<tr>
<td style="text-align: center;" colspan="2" rowspan="2">another 2x2</td>
<td style="text-align: right;">+</td>
</tr>
<tr>
<td style="text-align: right;"></td>
<td></td>
<td>!</td>
</tr>
</tbody>
</table>
</div>
```
| >     | >           |   Colspan    | >           | for thead |
| ----- | :---------: | -----------: | ----------- | --------- |
| Lorem | ipsum       |    dolor     | sit         | amet      |
| ^     | -           |      >       | right align | .         |
| ,     | >           | center align | >           | 2x2 cell  |
| >     | another 2x2 |      +       | >           | ^         |
| >     | ^           |              |             | !         |
```

### Disable
Disable tables

```php
$Parsedown = new ParsedownExtended([
    'tables' => false
]);
```

### Enable Tablespan
Enable tablespan

```php
$Parsedown = new ParsedownExtended([
    'tables' => {
        'tablespan' => true
    }
]);
```
