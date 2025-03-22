<?php

/**
 * Cleans and normalizes a multi-dimensional array by:
 * - Sorting the array by keys
 * - Removing null, empty, and duplicate values
 * - Applying a custom array mapping function (if provided)
 * - Recursively cleaning nested arrays
 *
 * @param array $array The input array to be cleaned
 * @param callable|null $array_map_function An optional function to apply to each array element
 * @return array The cleaned and normalized array
 */
function cleanArray($array, $array_map_function = null)
{
    // Early return for empty arrays
    if (empty($array)) {
        return [];
    }

    // Sort array by keys first
    ksort($array);

    // Check if we're at the lowest level (no nested arrays)
    if (count(array_filter($array, 'is_array')) === 0) {
        // Combine remove accents, trim, and custom function into a single array_map call
        $array = array_map(function ($value) use ($array_map_function) {
            $value = removeAccents($value); // Remove diacritical marks from characters
            $value = trim($value); // Remove whitespace from beginning and end
            if (is_callable($array_map_function)) {
                $value = $array_map_function($value); // Apply custom function (e.g., strtolower)
            }
            return $value;
        }, $array);

        // Remove duplicates and sort naturally
        $array = array_unique($array); // Remove duplicate values
        natcasesort($array); // Sort array using a case-insensitive "natural order" algorithm
        $array = array_values($array); // Reset numeric keys
        $array = array_filter($array); // Remove empty values
        return $array;
    }

    // Process nested arrays recursively
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            // Recursively clean nested arrays
            $array[$key] = cleanArray($value, $array_map_function);
            // Remove empty arrays after cleaning
            if (empty($array[$key])) {
                unset($array[$key]);
            }
        } elseif (is_null($value) || trim($value) === '' || $value === []) {
            // Remove null, empty strings, or empty arrays
            unset($array[$key]);
        }
    }

    return $array;
}

/**
 * Recursively flattens a multi-dimensional array and implodes the values into a single string.
 * This is useful for converting complex array structures into a simple comma-separated string.
 *
 * @param array $array The input multi-dimensional array.
 * @return string The imploded string of all values in the array, separated by commas.
 */
function implodeRecursively(array $array)
{
    $flattened = [];
    // Walk through the array recursively and collect all leaf values
    array_walk_recursive($array, function ($value) use (&$flattened) {
        $flattened[] = $value;
    });

    // Join all values with commas
    return implode(', ', $flattened);
}

/**
 * Case-insensitive version of array_unique.
 * Removes duplicate values from an array, ignoring case differences.
 *
 * For example, ['Apple', 'apple', 'APPLE'] would be reduced to just ['Apple'].
 *
 * @param array $array The input array with potential case-insensitive duplicates
 * @return array The array with case-insensitive duplicates removed
 */
function array_iunique($array)
{
    return array_intersect_key(
        $array,
        array_unique(array_map("strtolower", $array))
    );
}
