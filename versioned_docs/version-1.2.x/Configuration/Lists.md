---
title: Lists
---

# Lists

## Description

Lists in Markdown documents are versatile, allowing for the creation of both ordered and unordered lists, along with specialized task lists. These can be nested to various levels to organize content efficiently. ParsedownExtended enhances the handling of lists by providing configurable options to manage how they are processed, including enabling or disabling them entirely or tweaking the behavior of task lists specifically.

## Configuration Syntax

To configure list processing in ParsedownExtended, use the `setSetting` method with appropriate parameters:

```php
$ParsedownExtended->setSetting('lists', (boolean|array) $value);
```

- `$value` can be a boolean to enable (`true`) or disable (`false`) list processing altogether, or an array to configure specific types of lists, such as task lists.

## Examples

### Basic List Example

A simple unordered list in Markdown looks like this:

```markdown
- First ordered list item.
- Second ordered list item.
- Third ordered list item.
- Fourth ordered list item.
- Fifth ordered list item.
```

### Disabling Lists

To disable list processing:

```php
$ParsedownExtended->setSetting('lists', false);
```

### Task Lists

Task lists allow for interactive checkboxes in your lists, ideal for to-do lists or checklists:

```markdown
- [x] Write the press release
- [ ] Update the website
- [ ] Contact the media
```

To disable task list processing:

```php
$ParsedownExtended->setSetting('lists.tasks' => false);
```