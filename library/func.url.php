<?php

/**
 * Path and URL parsing functions
 */

/**
 * Get the current URL.
 *
 * This function constructs the current URL based on the server protocol (HTTP or HTTPS)
 * and the host name. It optionally includes the port number if it is not the default port
 * for the protocol.
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
 * Get the directory path of the current script.
 *
 * This function returns the directory path of the current script,
 * trimming any trailing slashes.
 *
 * @return string The directory path
 */
function dirPath()
{
    return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
}

/**
 * Get the file path of the current request.
 *
 * This function returns the file path part of the current request URI.
 *
 * @return string The file path
 */
function filePath()
{
    return $_SERVER['REQUEST_URI'];
}

/**
 * Combine the current URL and the file path.
 *
 * This function combines the current URL (optionally including the port number)
 * with the file path of the current request.
 *
 * @param bool $includePort Whether to include the port number in the URL
 * @return string The full URL including the file path
 */
function urlFilePath($includePort = false)
{
    return url($includePort) . filePath();
}

/**
 * Combine the current URL and the directory path.
 *
 * This function combines the current URL (optionally including the port number)
 * with the directory path of the current script.
 *
 * @param bool $includePort Whether to include the port number in the URL
 * @return string The full URL including the directory path
 */
function urlDirPath($includePort = false)
{
    return url($includePort) . dirPath();
}

/**
 * Build a URL with query parameters.
 *
 * This function appends query parameters to a base URL. If the base URL already
 * contains query parameters, the new parameters are appended with an '&'.
 * Otherwise, they are appended with a '?'.
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
 * Check if the current request is using HTTPS.
 *
 * This function checks if the current request is being made over HTTPS.
 *
 * @return bool True if HTTPS is being used, false otherwise
 */
function isHttps()
{
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}
