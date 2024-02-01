---
layout: page
---

[![Github All Releases](https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtended.svg?style=flat-square)](https://github.com/BenjaminHoegh/ParsedownExtended/releases) [![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownExtended?style=flat-square)](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/LICENSE.md)

## Introduction

ParsedownExtended is an extention for Parsedown, offering additional features and functionalities. It is designed to provide an easy-to-use Markdown parsing solution while extending the capabilities of the base Parsedown library.

## Features

ParsedownExtended includes a variety of features to enhance your Markdown parsing experience:

- **Task Lists:** Create simple task lists in Markdown.
- **Smartypants:** Automatically convert straight quotes to curly, dashes to en-dash and em-dash, etc.
- **Emojis:** Support for rendering emojis.
- **Heading Permalinks:** Generate permalinks for your headings.
- **Table of Contents:** Automatically generate a table of contents based on headings.
- **Keystrokes:** Render keystroke combinations.
- **Marking:** Mark text within your documents for emphasis or distinction.
- **Superscript and Subscript:** Render text as superscript or subscript.
- **Diagrams Syntax Support:** Recognizes diagram syntax for integration with libraries like mermaid.js and chart.js.
- **LaTeX Syntax Support:** Detects LaTeX syntax, suitable for mathematical expressions, to be rendered with libraries like KaTeX.js.
- **Predefined Abbreviations:** Define and use abbreviations easily.
- **Customizable Options:** Extensive options for customizing each Markdown element.
- **Additional Features:** ParsedownExtended continuously evolves, adding more features over time.

## Getting started

### Manual Installation

1. Download the latest version of `ParsedownExtended` from the [releases page](https://github.com/BenjaminHoegh/ParsedownExtended/releases).
2. Include the required files in your project:

    ```php
    require 'Parsedown.php';
    require 'ParsedownExtra.php'; // optional
    require 'ParsedownExtended.php';

    $ParsedownExtended = new ParsedownExtended();

    echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
    // you can also parse inline markdown only
    echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
    ```



### Using Composer

1. Install via Composer:

    ```shell
    composer require BenjaminHoegh/parsedown-extended
    ```

2. Include the Composer autoloader in your project:

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

Code and documentation copyright 2024 the [ParsedownExtended Authors](https://github.com/BenjaminHoegh/ParsedownExtended/graphs/contributors). Code released under the [MIT License](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/LICENSE.md). Docs released under [Creative Commons](https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/docs/LICENSE.md).
