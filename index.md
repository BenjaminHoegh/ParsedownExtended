---
layout: page
---

[![Github All Releases](https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtended.svg?style=flat-square)](https://github.com/BenjaminHoegh/ParsedownExtended/releases) [![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownExtended?style=flat-square)](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/LICENSE.md)

Table of contents

- [Getting started](#getting-started)
- [Bugs and feature requests](#bugs-and-feature-requests)
- [Contributing](#contributing)
- [Community](#community)
- [Copyright and license](#copyright-and-license)

## Features
- Task
- Smartypants
- Emojis
- Heading permalink
- Table of content
- Keystrokes
- Highlight
- Super and subscript
- Diagrams
- LaTeX
- Predefined abbreviation
- Options for every element of markdown
- And more...

## Getting started

### Manuel
Download the source code from the latest release
You must include <code class="file">parsedown.php</code>
and <code class="file">ParsedownExtended.php</code>

```php
require 'Parsedown.php';
require 'ParsedownExtra.php'; // optional
require 'ParsedownExtended.php';

$ParsedownExtended = new ParsedownExtended();

echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```

### Using composer

From the command line interface, navigate to your project folder then run this command:
<code class="console">composer require BenjaminHoegh/parsedown-extended</code>

Then require the auto-loader file:
```php
require 'vendor/autoload.php';

$ParsedownExtended = new ParsedownExtended();

echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```

## Bugs and feature requests

Have a bug or a feature request? Please first read the [issue guidelines](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/.github/CONTRIBUTING.md#using-the-issue-tracker) and search for existing and closed issues. If your problem or idea is not addressed yet, [please open a new issue](https://github.com/BenjaminHoegh/ParsedownExtended/issues/new/choose).

## Contributing

Please read through our [contributing guidelines](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/.github/CONTRIBUTING.md). Included are directions for opening issues, coding standards, and notes on development.

All PHP should conform to the [Code Guide](https://www.php-fig.org/psr/psr-12/).

## Community

Get updates on ParsedownExtended's development and chat with the project maintainers and community members.

- Join [GitHub discussions](https://github.com/BenjaminHoegh/ParsedownExtended/discussions).

## Copyright and license

Code and documentation copyright 2021 the [ParsedownExtended Authors](https://github.com/BenjaminHoegh/ParsedownExtended/graphs/contributors). Code released under the [MIT License](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/LICENSE.md). Docs released under [Creative Commons](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/docs/LICENSE.md).
