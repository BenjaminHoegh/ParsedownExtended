---
title: Installation
sidebar_position: 2
---

# Installation


## Requirements

Before you start using ParsedownExtended, ensure that your system meets the following requirements:

- **PHP Version:** PHP 7.4 or higher. ParsedownExtended relies on the latest PHP features and improvements.
- **Parsedown:** The original Parsedown library version 1.7 or higher is required. This is automatically managed via Composer if you install ParsedownExtended through Composer.
- **ParsedownExtra:** If you want to use the ParsedownExtra features, you need to have the ParsedownExtra library version 0.8 or higher. This is automatically managed via Composer if you install ParsedownExtended through Composer.

Meeting these requirements will ensure that ParsedownExtended functions correctly and efficiently on your system.

## Using composer

From the command line interface, navigate to your project folder then run this command:
```shell
composer require benjaminhoegh/parsedown-extended
```
Then require the auto-loader file:
```php
require 'vendor/autoload.php';

$ParsedownExtended = new ParsedownExtended();

echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```


## Manual Installation

Download the source code from the latest release, then include the necessary files in your project:

```php
require 'Parsedown.php';
require 'ParsedownExtra.php'; // optional
require 'ParsedownExtended.php';

$ParsedownExtended = new ParsedownExtended();

echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```