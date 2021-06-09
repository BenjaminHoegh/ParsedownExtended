## Meet ParsedownExtended

ParsedownExtended is a extension for Parsedown adding a lot of new feature while strives to provide an amazing developer experience while providing powerful features

Whether you are new to PHP or web frameworks or have years of experience, ParsedownExtended is easy to get started with. We'll help you take your first steps or give you a boost as you take your expertise to the next level. We can't wait to see what you build.

## Getting started

### Manuel
Download the source code from the latest release
You must include `parsedown.php` or `parsedownExtra.php`
Include `ParsedownExtended.php`

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
```shell
composer require BenjaminHoegh/parsedown-extended
```
Then require the auto-loader file:
```php
require 'vendor/autoload.php';

$ParsedownExtended = new ParsedownExtended();

echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```
