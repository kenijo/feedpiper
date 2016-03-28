<?php

/**
 * URL parsing functions
 */

/**
 * Get URL
 */
function url()
{
  return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}";
}

/**
 * Get directory path
 */
function dir_path()
{
  return dirname(file_path());
}

/**
 * Get file path
 */
function file_path()
{
  return $_SERVER['REQUEST_URI'];
}

/**
 * Combine URL and file path
 */
function url_file_path()
{
  return url() . file_path();
}

/**
 * Combine URL and directory path
 */
function url_dir_path()
{
  return url() . dir_path();
}
