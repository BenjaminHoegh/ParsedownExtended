---
layout: default
title: Settings Method
---


## `setSetting` Method

The `setSetting` method allows you to set a setting value for the ParsedownExtended instance. It is designed to be chainable, allowing you to easily configure settings.

### Method Signature
```php
public function setSetting(string $settingName, $settingValue): self
```

### Parameters

- `settingName` (string): The name of the setting to be updated, specified in dot notation.
- `settingValue` ($settingValue): The value to be set for the specified setting.

### Return Value

The method returns the current instance of the `ParsedownExtended` class, which allows for method chaining.

### Usage Examples

```php
$Parsedown = new ParsedownExtended();

// Example 1: Updating a boolean setting 'code' to true
$Parsedown->setSetting('code', true);

// Example 2: Updating a boolean setting 'emphasis.subscript' to true
$Parsedown->setSetting('emphasis.subscript', true);

// Example 3: Updating a setting with an array value while preserving 'enabled'
$Parsedown->setSetting('math.block.delimiters', [
    ['left' => 'kk', 'right' => 'dd'],
    ['left' => '__', 'right' => '__']
]);

// Example 4: Updating a setting value with method chaining
$Parsedown
    ->setSetting('headings.allowed', ['h1', 'h2'])
    ->setSetting('links.enabled', true);
```

### Description

- The `settingName` parameter allows you to specify the setting to be updated, using dot notation to indicate nested settings.
- The `settingValue` parameter can be a boolean, string or an array. If it is a boolean, and the specified setting contains an 'enabled' key, it will update the 'enabled' key. If it is an array, it will update the setting while preserving the 'enabled' key if present.

### Note

- If the specified `settingName` does not exist in the settings hierarchy, the method returns the current instance without making any changes.

### Example

```php
$Parsedown = new ParsedownExtended();

// Example: Updating a boolean setting 'code' to true
$Parsedown->setSetting('code', true);

// After this operation, the 'code' setting in $ParsedownExtended's settings array will be:
// 'code' => [
//     'enabled' => true,
//     // other 'code' settings...
// ]
```
