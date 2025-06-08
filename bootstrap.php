<?php

// Load Parsedown 1.x files
require_once '/tmp/Parsedown.php';
require_once '/tmp/ParsedownExtra.php';

// Create a simple autoloader for our namespace
spl_autoload_register(function ($class) {
    if (strpos($class, 'BenjaminHoegh\ParsedownExtended\\') === 0) {
        $classFile = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('BenjaminHoegh\ParsedownExtended\\')));
        $file = __DIR__ . '/src/' . $classFile . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});