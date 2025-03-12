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
            $value = removeAccents($value);
            $value = trim($value);
            if (is_callable($array_map_function)) {
                $value = $array_map_function($value);
            }
            return $value;
        }, $array);

        // Remove duplicates and sort naturally
        $array = array_unique($array);
        natcasesort($array);
        $array = array_values($array);
        $array = array_filter($array);
        return $array;
    }

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = cleanArray($value, $array_map_function);
            if (empty($array[$key])) {
                unset($array[$key]);
            }
        } elseif (is_null($value) || trim($value) === '' || $value === []) {
            unset($array[$key]);
        }
    }

    return $array;
}

/**
 * Recursively flattens a multi-dimensional array and implodes the values into a single string.
 *
 * @param array $array The input multi-dimensional array.
 * @return string The imploded string of all values in the array.
 */
function implodeRecursively(array $array)
{
    $flattened = [];
    array_walk_recursive($array, function ($value) use (&$flattened) {
        $flattened[] = $value;
    });

    return implode(', ', $flattened);
}
