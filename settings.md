### Documentation and Developer Guidance

**SettingsManager System Documentation**

- **Introduction**: The `SettingsManager` provides a flexible and robust system for managing application settings. It supports nested settings, type validation, and batch updates, ensuring configurations adhere to a predefined schema.

- **Schema Definition**: Define your settings schema with types, default values, and structures. For array settings, specify the expected structure of array items if necessary.

- **Updating Settings**:
  - **Single Setting**: Use `set('setting_name', value)` to update a single setting. For boolean settings, this can enable/disable features directly.
  - **Batch Updates**: Pass an associative array to `set` to update multiple settings at once, e.g., `set(['setting1' => value1, 'setting2' => value2])`.
  - **Nested Settings**: For nested settings, use dot notation in keys, e.g., `set('nested.setting', value)`.

- **Retrieving Settings**: Use `get('setting_name')` to retrieve the current value of a setting. For nested settings, use dot notation.

- **Checking Enabled State**: `isEnabled('feature')` checks if a feature is enabled. This works for both top-level and nested settings by evaluating the `enabled` state.

- **Type Validation**: The system ensures that values match their defined types in the schema. For arrays, it validates against the specified structure for array items.

- **Error Handling**: Invalid operations, such as setting an undefined setting or mismatched types, throw `InvalidArgumentExceptions`.

- **Examples**: Provide examples for common operations, like enabling a feature, updating nested settings, and handling array settings.
