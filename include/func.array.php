<?php

/**
 * Array parsing functions
 */

/**
 * Function to clean arrays recursively
 */
function cleanArray($array, $function = null)
{
  if(is_array($array))
  {
    $result = Array();

    // Check if the array contains arrays
    if (count($array) !== count($array, COUNT_RECURSIVE))
    {
      foreach ($array as $key => $subArray)
      {
        $result[$key] = cleanArray($subArray, $function);
      }
    }
    else
    {
      $result = $array;
      $result = array_map('remove_accents', $result);

      if($function)
      {
        $result = array_map($function, $result);
      }

      $result = array_unique($result);
      $result = array_filter($result);
      sort($result);
    }
    return $result;
  }
}

/**
 * Function to get an array as a comma separated string
 */
function get_array_as_string($array)
{
  if(is_array($array))
  {
    $result = Array();
    // Flattens $array and put it in $result
    array_walk_recursive($array, function($value, $key) use (&$result){ $result[] = $value; });
    return implode(', ', cleanArray($result));
  }
}
