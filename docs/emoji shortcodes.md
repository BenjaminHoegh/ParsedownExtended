---
layout: default
title: Emoji Shortcodes
---

# Emoji Shortcodes

## Syntax

```php
"emojis" => (boolean) $value // default true
```

## Description
Allows you to insert emoji by typing emoji shortcodes. These begin and end with a colon and include the name of an emoji.


<div class="code-example">
Gone camping! ⛺ Be back soon.<br><br>

That is so funny! 😂
</div>
```markdown
Gone camping! :tent: Be back soon.

That is so funny! :joy:
```

## Examples

### Enable
Enable emojis shortcodes
```php
$Parsedown = new ParsedownExtended([
    "emojis" => true
]);
```
