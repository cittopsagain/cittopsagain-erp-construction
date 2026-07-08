<?php

// Register an autoloader to automatically load classes based on their namespace.
// It maps namespaces to directory paths (e.g., Core\App -> Core/App.php).
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Instantiate the Core App class to process the request.
$app = new \Core\App();
