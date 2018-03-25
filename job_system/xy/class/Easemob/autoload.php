<?php

spl_autoload_register(function ($class) {
    $base_dir = defined('EASEMOB_SDK_SRC_DIR') ? EASEMOB_SDK_SRC_DIR : __DIR__ . '/src/';
    $prefix = 'Easemob\\';
    $len = strlen($prefix);
    if (strncmp($class, $prefix, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    if (is_readable($file)) {
        require $file;
    }
});
