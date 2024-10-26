<?php

/**
 * URL parsing functions
 */

/**
 * Get URL
 *
 * @param bool $includePort Whether to include the port number in the URL
 * @return string The current URL
 */
function url($includePort = false)
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    if ($includePort && !in_array($_SERVER['SERVER_PORT'], [80, 443])) {
        $host .= ':' . $_SERVER['SERVER_PORT'];
    }

    return "{$protocol}://{$host}";
}

/**
 * Get directory path
 *
 * @return string The directory path
 */
function dirPath()
{
    return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
}

/**
 * Get file path
 *
 * @return string The file path
 */
function filePath()
{
    return $_SERVER['REQUEST_URI'];
}

/**
 * Combine URL and file path
 *
 * @param bool $includePort Whether to include the port number in the URL
 * @return string The full URL including file path
 */
function urlFilePath($includePort = false)
{
    return url($includePort) . filePath();
}

/**
 * Combine URL and directory path
 *
 * @param bool $includePort Whether to include the port number in the URL
 * @return string The full URL including directory path
 */
function urlDirPath($includePort = false)
{
    return url($includePort) . dirPath();
}

/**
 * Build a URL with query parameters
 *
 * @param string $url The base URL
 * @param array $params An associative array of query parameters
 * @return string The URL with query parameters
 */
function buildUrl($url, array $params = [])
{
    if (empty($params)) {
        return $url;
    }

    $query = http_build_query($params);
    $separator = (parse_url($url, PHP_URL_QUERY) === null) ? '?' : '&';

    return $url . $separator . $query;
}

/**
 * Check if the current request is using HTTPS
 *
 * @return bool True if HTTPS is being used, false otherwise
 */
function isHttps()
{
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}
