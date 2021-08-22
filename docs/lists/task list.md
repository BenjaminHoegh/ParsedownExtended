---
layout: default
title: Task list
parent: Lists
---

# Task

## Syntax

```php
"task" => (boolean) $value // default true
```

## Description
Task lists allow you to create a list of items with checkboxes. checkboxes will be displayed next to the content. To create a task list, add dashes (-) and brackets with a space ([ ]) in front of task list items. To select a checkbox, add an x in between the brackets ([x]).

<div class="code-example">
<ul class="task-list">
  <li class="task-list-item"><input type="checkbox" class="task-list-item-checkbox" disabled="disabled" checked="checked">Write the press release</li>
  <li class="task-list-item"><input type="checkbox" class="task-list-item-checkbox" disabled="disabled">Update the website</li>
  <li class="task-list-item"><input type="checkbox" class="task-list-item-checkbox" disabled="disabled">Contact the media</li>
</ul>
</div>
```markdown
- [x] Write the press release
- [ ] Update the website
- [ ] Contact the media
```

## Examples

### Disable
Disable tables

```php
$Parsedown = new ParsedownExtended([
    'lists' => {
        'task' => false
    }
]);
```
