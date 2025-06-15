# PHP Compatibility Checking

ParsedownExtended includes tools to check PHP compatibility across different PHP versions. This helps ensure that the code works correctly on the PHP versions it claims to support.

## Setup

The project is configured to use [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility) with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) to check compatibility with PHP 7.4 and higher.

## How to Check PHP Compatibility

1. Make sure you have installed the development dependencies:
   ```shell
   composer install --dev
   ```

2. Run the PHP compatibility check:
   ```shell
   composer check-php-compatibility
   ```

This will analyze the codebase and report any compatibility issues with PHP 7.4 and higher versions.

## Customizing PHP Version Checks

If you need to check compatibility with a specific PHP version, you can modify the `testVersion` value in the `phpcs.xml` file:

```xml
<!-- Check for compatibility with PHP 7.4 and higher -->
<config name="testVersion" value="7.4-"/>
```

For example, to check compatibility with PHP 8.0 to 8.2:

```xml
<config name="testVersion" value="8.0-8.2"/>
```

## Common Issues

Here are some common PHP compatibility issues to watch for:

1. **Deprecated Functions**: Functions that have been deprecated in newer PHP versions.
2. **Removed Functions**: Functions that have been removed in newer PHP versions.
3. **Syntax Changes**: Changes in PHP syntax that might cause code to break.
4. **Parameter Changes**: Changes in function parameters or return types.

## Further Reading

- [PHPCompatibility Documentation](https://github.com/PHPCompatibility/PHPCompatibility)
- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [PHP Supported Versions](https://www.php.net/supported-versions.php)
