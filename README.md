<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownExtended">
    <img alt="ParsedownExtended" src="https://github.com/BenjaminHoegh/ParsedownExtended/blob/docs/parsedownExtended.png" height="330" />
  </a>
</p>

<h3 align="center">ParsedownExtended</h3>

<p align="center">
  A lightweight Parsedown extension with practical Markdown features and configurable output.
</p>

<p align="center">
  <a href="https://benjaminhoegh.github.io/ParsedownExtended/">Documentation</a>
  ·
  <a href="https://github.com/BenjaminHoegh/ParsedownExtended/issues/new/choose">Report an issue</a>
  ·
  <a href="https://github.com/BenjaminHoegh/ParsedownExtended/discussions">Discussions</a>
</p>

<br>

![GitHub Release](https://img.shields.io/github/v/release/BenjaminHoegh/ParsedownExtended?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/benjaminhoegh/parsedown-extended?style=flat-square)
![GitHub License](https://img.shields.io/github/license/BenjaminHoegh/ParsedownExtended?style=flat-square)

## About

ParsedownExtended builds on [Parsedown](https://github.com/erusev/parsedown) and adds commonly requested Markdown features such as task lists, alerts, heading anchors, table of contents generation, math notation detection, emoji shortcodes, typographic replacements, and more.

Standalone versions of some extended features are also available as separate libraries:

* [Parsedown Toc](https://github.com/BenjaminHoegh/ParsedownToc)
* [Parsedown Math](https://github.com/BenjaminHoegh/ParsedownMath)

## Requirements

* PHP 8.2 or later
* Parsedown 1.8 or later
* Parsedown Extra 0.9 or later when using Composer-installed dependencies

## Getting started

Install with Composer:

```bash
composer require benjaminhoegh/parsedown-extended
```

Then include Composer's autoloader and create a parser instance:

```php
require 'vendor/autoload.php';

use BenjaminHoegh\ParsedownExtended\ParsedownExtended;

$parsedown = new ParsedownExtended();

echo $parsedown->text('Hello _Parsedown_!');
```

For inline Markdown only, use the inherited `line()` method:

```php
echo $parsedown->line('Hello _Parsedown_!');
```

## Configuration

ParsedownExtended exposes a configuration handler through `config()`.

```php
$parsedown = new ParsedownExtended();

$parsedown->config()->set('toc', true);
$parsedown->config()->set('toc.levels', ['h1', 'h2', 'h3']);
$parsedown->config()->set('math.enabled', true);
$parsedown->config()->set('allow_raw_html', false);
```

Grouped configuration is also supported:

```php
$parsedown->config()->set('toc', [
    'levels' => ['h1', 'h2', 'h3'],
    'tag' => '[TOC]',
    'id' => 'table-of-contents',
]);
```

For the full supported API and configuration paths, see the [Documentation](https://benjaminhoegh.github.io/ParsedownExtended/).

## Table of contents

Use `text()` when you want `[TOC]` replaced automatically:

```php
echo $parsedown->text($markdown);
```

Use `body()` and `contentsList()` when you want to render the document and table of contents separately:

```php
$body = $parsedown->body($markdown);
$toc = $parsedown->contentsList();

echo $toc;
echo $body;
```

## Security

ParsedownExtended is a Markdown parser extension, not an HTML sanitizer.

If you render Markdown from untrusted users, review your `allow_raw_html` setting and sanitize the generated HTML according to your application's threat model.

Please do not open public issues for suspected security vulnerabilities. See [SECURITY.md](SECURITY.md) for reporting instructions.

## Bugs and feature requests

Before opening an issue, please search existing and closed issues.

* Use issues for reproducible bugs.
* Use discussions for questions, ideas, and support.
* Include a minimal reproduction when reporting bugs.

Open a new issue here: [Issues](https://github.com/BenjaminHoegh/ParsedownExtended/issues/new/choose)

## Contributing

Contributions are welcome, especially focused bug fixes, documentation improvements, and small compatibility fixes.

Before starting large features or refactors, please open a discussion first so the scope can be agreed before work begins.

Please read the [contributing guidelines](https://github.com/BenjaminHoegh/ParsedownExtended/blob/main/.github/CONTRIBUTING.md) before opening a pull request.

## Community

Join [GitHub Discussions](https://github.com/BenjaminHoegh/ParsedownExtended/discussions) for questions, ideas, and project discussion.

## License

Code is released under the [MIT License](https://github.com/BenjaminHoegh/ParsedownExtended/blob/main/LICENSE.md).

Documentation is released under [Creative Commons](https://github.com/BenjaminHoegh/ParsedownExtended/blob/main/docs/LICENSE.md).
