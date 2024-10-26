<?php

/**
 * Array parsing functions
 */

/**
 * Function to clean arrays recursively
 */
function cleanArray($array, $function = null)
{
    if (!is_array($array)) {
        return [];
    }

    $result = [];

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result[$key] = cleanArray($value, $function);
        } else {
            $result[$key] = removeAccents($value);
            if ($function) {
                $result[$key] = $function($result[$key]);
            }

            $result = array_unique($result);
            $result = array_filter($result);
            sort($result);
        }
    }

    return $result;
}

/**
 * Get an array as a comma-separated string
 *
 * @param array $array The input array
 * @return string The comma-separated string
 */
function convertArrayToCommaSeparatedString(array $array)
{
    $flattened = [];
    array_walk_recursive($array, function ($value) use (&$flattened) {
        $flattened[] = $value;
    });

    return implode(', ', cleanArray($flattened));
}
