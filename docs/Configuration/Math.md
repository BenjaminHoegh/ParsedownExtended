---
title: Math
---

# Math

## Description

ParsedownExtended introduces support for LaTeX within Markdown, allowing you to incorporate mathematical expressions and notation seamlessly into your documents. By identifying and preserving LaTeX syntax, ParsedownExtended facilitates the use of client-side rendering libraries like [KaTeX](https://katex.org) to render these expressions. This feature is invaluable for academic, scientific, and technical documentation where complex mathematical formulas need to be clearly presented.

$$
I = \int_0^{2\pi} \sin(x)\,dx
$$

## Configuration Syntax

To enable LaTeX support in ParsedownExtended, use the `config()->set()` method:

```php
$ParsedownExtended->config()->set('math', (bool|array) $value);
```

This configuration can be set to `true` to enable LaTeX processing, or more detailed options can be specified through an array.

## Parameters

The `math` feature allows the following configurations:

- **enabled** (boolean): Enable or disable LaTeX processing globally.
- **inline** (array): Configure inline math expressions.
  - **delimiters** (array): Define the delimiters for inline math expressions.
- **block** (array): Configure block math expressions.
  - **delimiters** (array): Define the delimiters for block math expressions.

> Custom delimiters may not work as expected when using characters not registered in `$specialCharacters`.

## Examples

### Enable LaTeX Support

To activate LaTeX processing, allowing Markdown to include LaTeX expressions for client-side rendering:

```php
$ParsedownExtended->config()->set('math', true);
```

### Configure Inline and Block Math Separately

To configure inline and block math processing separately:

```php
$ParsedownExtended->config()->set('math', [
    'inline' => [
        'delimiters' => [
            ['left' => '\\(', 'right' => '\\)'],
        ],
    ],
    'block' => [
        'delimiters' => [
            ['left' => '$$', 'right' => '$$'],
            ['left' => '\\begin{equation}', 'right' => '\\end{equation}'],
            ['left' => '\\begin{align}', 'right' => '\\end{align}'],
            ['left' => '\\begin{alignat}', 'right' => '\\end{alignat}'],
            ['left' => '\\begin{gather}', 'right' => '\\end{gather}'],
            ['left' => '\\begin{CD}', 'right' => '\\end{CD}'],
            ['left' => '\\[', 'right' => '\\]'],
        ],
    ],
]);
```

This documentation provides users with clear instructions on how to enable and configure LaTeX support for mathematical expressions within their Markdown content using the full context of `$ParsedownExtended->config()->set()`.