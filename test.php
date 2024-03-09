<?php

class SettingsManager
{
    private $settings = [];
    private $settingsSchema = [];

    public function __construct($settingsSchema)
    {
        $this->settingsSchema = $settingsSchema;
        $this->initializeSettings();
    }

    private function initializeSettings()
    {
        foreach ($this->settingsSchema as $key => $schema) {
            if (isset($schema['type'])) {
                $this->settings[$key] = $schema['default'];
            } else {
                $this->settings[$key] = [];
                $this->initializeSettingsRecursive($this->settings[$key], $schema);
            }
        }
    }

    private function initializeSettingsRecursive(&$settings, $schema)
    {
        foreach ($schema as $key => $value) {
            if (isset($value['type'])) {
                $settings[$key] = $value['default'];
            } else {
                $settings[$key] = [];
                $this->initializeSettingsRecursive($settings[$key], $value);
            }
        }
    }

    public function settings()
    {
        return new class ($this->settings, $this->settingsSchema) {
            private $settings;
            private $schema;

            public function __construct(&$settings, $schema)
            {
                $this->settings = &$settings;
                $this->schema = $schema;
            }

            public function set($keyPath, $value)
            {
                $keys = explode('.', $keyPath);
                /** @psalm-suppress UnsupportedPropertyReferenceUsage */
                $current = &$this->settings;
                $schema = $this->schema;

                foreach ($keys as $index => $key) {
                    if (!isset($schema[$key])) {
                        throw new InvalidArgumentException("Setting '$keyPath' is not defined in the schema.");
                    }
                    if ($index < count($keys) - 1) {
                        $current = &$current[$key];
                        $schema = $schema[$key];
                    } else {
                        if ($value === true || $value === false) {
                            if (!isset($current[$key]['enabled'])) {
                                $current[$key] = ['enabled' => $value];
                            } else {
                                $current[$key]['enabled'] = $value;
                            }
                        } else {
                            $current[$key] = $value;
                        }
                    }
                }

                return $this;
            }

            public function get($keyPath)
            {
                $keys = explode('.', $keyPath);
                $current = $this->settings;

                foreach ($keys as $key) {
                    if (!isset($current[$key])) {
                        throw new InvalidArgumentException("Setting '$keyPath' does not exist.");
                    }
                    $current = $current[$key];
                }

                // Exclude 'enabled' from the returned value
                if (is_array($current) && array_key_exists('enabled', $current)) {
                    unset($current['enabled']);
                }

                return $current;
            }

            public function isEnabled($keyPath)
            {
                $current = $this->settings;
                $keys = explode('.', $keyPath);

                foreach ($keys as $key) {
                    if (!isset($current[$key])) {
                        return false; // Setting not found, considered disabled
                    }
                    $current = $current[$key];
                }

                if (is_array($current) && isset($current['enabled'])) {
                    return $current['enabled'];
                }

                return false; // No explicit 'enabled' key, considered disabled
            }

            public function add($keyPath, $value)
            {
                $keys = explode('.', $keyPath);
                /** @psalm-suppress UnsupportedPropertyReferenceUsage */
                $current = &$this->settings;

                foreach ($keys as $index => $key) {
                    if ($index < count($keys) - 1) {
                        if (!isset($current[$key])) {
                            throw new InvalidArgumentException("Cannot navigate to '$key' in '$keyPath'.");
                        }
                        $current = &$current[$key];
                    } else {
                        if (!is_array($current[$key])) {
                            $current[$key] = [];
                        }
                        $current[$key][] = $value;
                    }
                }

                return $this;
            }
        };
    }
}
