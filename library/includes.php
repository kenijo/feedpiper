<?php

/**
 * Inclusion of all the configuration and library files
 *
 * This script defines the base path for the application and includes all necessary
 * configuration files, internal library files, and external libraries. It ensures that
 * all required files are loaded before the application starts.
 */

// Define the base path for the application
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// List of configuration files to be included
$configFiles = [
    'config/feedfilter.conf.php',       // Configuration for feed filtering
    'config/html2feed.conf.php',        // Configuration for HTML to feed conversion
];

// List of internal library files to be included
$libFiles = [
    'library/class.feed.php',           // Class for feed creation and management
    'library/class.feedentry.php',      // Class for feed entry creation and management
    'library/func.array.php',           // Array utility functions
    'library/func.curl-emu.php',        // cURL emulation functions
    'library/func.rest.php',            // REST API functions
    'library/func.string.php',          // String utility functions
    'library/func.url.php',             // URL utility functions
];

// List of external libraries to be included
$extLibs = [
    'vendor/erusev/parsedown/Parsedown.php',                    // Markdown parser
    'vendor/simplehtmldom/simplehtmldom/simple_html_dom.php',   // HTML DOM parser
    'vendor/simplepie/simplepie/autoloader.php',                // RSS and Atom feed parser
];

// Require all files
try {
    // Merge all file lists into one array
    $files = array_merge($configFiles, $libFiles, $extLibs);

    // Loop through each file and require it
    foreach ($files as $file) {
        $filePath = BASE_PATH . $file;
        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            // Throw an exception if the file does not exist
            throw new Exception("Required file not found: $filePath");
        }
    }
} catch (Exception $e) {
    // Handle exceptions by displaying an error message and stopping the script
    die("Error loading required files: " . $e->getMessage());
}
