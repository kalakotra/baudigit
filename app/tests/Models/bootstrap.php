<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
spl_autoload_register(function ($class) {
    if (strpos($class, 'app\\Models\\') === 0) {
        $file = __DIR__ . '/../../src/Models/' . substr($class, 11) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (strpos($class, 'Kalakotra\\Utils\\') === 0) {
        $file = __DIR__ . '/../../src/Utils/' . substr($class, 16) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
