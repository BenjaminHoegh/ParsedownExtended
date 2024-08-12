---
title: Emphasis
---

# Emphasis

## Description

Emphasis in Markdown is essential for highlighting text through italicization, boldening, or other emphasis methods. ParsedownExtended enhances your control over these emphasis styles, allowing for detailed customization or complete disabling of emphasis features to suit your document's needs.

## Available Emphasis Styles

ParsedownExtended supports a range of emphasis styles, which can be individually enabled or disabled:

- **bold:** Applies bold formatting.
- **italic:** Applies italic formatting.
- **marking:** Applies highlighting using the \<mark> tag.
- **strikethroughs:** Applies strikethrough formatting.
- **insertions:** Applies underline formatting typically used to indicate insertions.
- **subscript:** Applies subscript formatting.
- **superscript:** Applies superscript formatting.
- **keystrokes:** Applies formatting for keystrokes.

## Configuration Syntax

Configure emphasis features in ParsedownExtended using `setSetting`. You can enable or disable specific emphasis styles or turn off all emphasis processing:

```php
// To configure specific emphasis styles
$ParsedownExtended->setSetting('emphasis', [
    'bold' => true,  // Enable bold emphasis
    'italic' => true, // Enable italic emphasis
    // Specify other emphasis styles as needed
]);
```

This flexibility allows you to tailor the rendering of your Markdown content precisely.

## Examples

### Enable Specific Emphasis Styles

Enable only bold and marked text, while disabling others:

```php
$ParsedownExtended->setSetting('emphasis', [
    'bold' => true,
    'marking' => true,
]);
```

### Disable All Emphasis

To completely disable all forms of text emphasis:

```php
$ParsedownExtended->setSetting('emphasis', false);
```