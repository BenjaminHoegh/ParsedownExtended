---
layout: default
title: Code blocks
parent: Code blocks and snippets
---
# Code blocks

## Syntax
```php
"'blocks'" => (boolean) $value // default true
```

## Description
Formatting code blocks is useful when you have a bigger chunk of code to include in your Markdown file. Format your code blocks by indenting every line of your code block using one tab, or use three backticks (\`\`\`) before and after your code

## Examples

### Disable
Disable code blocks

```php
$Parsedown = new ParsedownExtended([
    'code' => {
        'blocks' => false
    }
]);
```

