<?php

/**
 * Inclusion of all the configuration and library files
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Configuration files
$configFiles = [
    'config/conf.php',
    'config/feedfilter.conf.php',
    'config/html2feed.conf.php',
];

// Library files
$libFiles = [
    'library/func.array.php',
    'library/func.curl-emu.php',
    'library/func.rest.php',
    'library/func.string.php',
    'library/func.url.php',
    'library/class.entry.php',
    'library/class.feed.php',
    'library/class.filteredfeed.php',
];

// External libraries
$extLibs = [
    'vendor/simplepie/autoloader.php',
    'vendor/simplehtmldom/simple_html_dom.php',
    'vendor/parsedown/Parsedown.php',
];

// Require all files
try {
    $files = array_merge($configFiles, $libFiles, $extLibs);

    foreach ($files as $file) {
        $filePath = BASE_PATH . $file;
        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            throw new Exception("Required file not found: $filePath");
        }
    }
} catch (Exception $e) {
    die("Error loading required files: " . $e->getMessage());
}
