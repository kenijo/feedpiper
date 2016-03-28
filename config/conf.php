<?php

/**
* Global Configuration
*/

/**
* Define if the feed produced should be ATOM or RSS compliant
*/
$cfg['feed_format'] = 'ATOM';

/**
* Extend execution time for the scripts to 60 seconds
* to avoid 'Maximum execution time exceeded' error
*/
//ini_set('max_execution_time', 60);

/**
* Define MySQL parameters database caching
* If not defined then file caching will be used
* Remember when using file caching that the cache folder must be writable (0766)
*/
$cfg['mysql']['user']     = 'user';
$cfg['mysql']['password'] = 'password';
$cfg['mysql']['host']     = 'localhost';
$cfg['mysql']['port']     = '3306';
$cfg['mysql']['database'] = 'feedpiper';

/**
* Set the length of time (in seconds)
* that the contents of a feed will be cached
* Default value is 3600 (1 hour)
*/
$cfg['cache_length'] = 3600;

/**
* Force SimplePie to use fsockopen() instead of cURL
* If cURL doesn't work, set variable to false to use fsockopen()
* Default value is true
*/
$cfg['curl'] = true;

/**
* Set the locale
*/
$cfg['locale'] = 'en_US';
