<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$documentRoot = __DIR__ . '/public';
$file = $documentRoot . $uri;

// Serve the requested resource as-is if it exists (static files)
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// Fallback to Symfony front controller for dynamic routes
require __DIR__ . '/public/index.php';