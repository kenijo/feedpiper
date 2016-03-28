<?php

/**
* ATOM Filter Configuration
*
* Used to merge feeds together and output a single feed.
* As well as filtering feed entries.
*/

/**
* Use the follwing pattern
* $cfg['example']['url']                                 = array('');
* $cfg['example']['title']                               = ',';
*
* 'example' represent the name of the attribute being passed on in the URL
* It can also be named 'global_filter' to work accross all the feeds defined.
* You can see it as an antispam for feeds
*
* Filter out (we remove what matches the following filter):
* $cfg['example']['filter']['title']['contains']         = array('');
*
* Filter Keep (we keep only what matches the following filter):
* Note that Filter Keep is always executed before Filter Out
* You decide what to keep and then remove out of the remaining entries
* $cfg['example']['filter']['keep']['title']['contains'] = array('');
*
*   title
*   link
*   content
*   author
*   category
*
*   Exclude Filters (delete what matches the expression):
*     starts   : equivalent to regex ^(.*)
*     contains : equivalent to regex \b(.*)\b
*     ends     : equivalent to regex (.*)$
*     regex    : any regex
*/

/**
* NOTE: Example with GitHub Trends
*
* Configure the application below and call it with
*   http://domain/feedpiper.php?feed=example
* You can debug the application by using
*   http://domain/feedpiper.php?feed=example&debug=true
*/
$cfg['example']['title'] = 'FEEDPIPER Example - GitHub Trends';
$cfg['example']['url']   = array(
'http://github-trends.ryotarai.info/rss/github_trends_all_daily.rss',
);
// Filter out
/*
$cfg['example']['filter']['title']['starts']      = array('words','that','will filter out an entry');
$cfg['example']['filter']['title']['contains']    = array('words','that','will filter out an entry');
$cfg['example']['filter']['title']['ends']        = array('words','that','will filter out an entry');
$cfg['example']['filter']['title']['regex']       = array('words','that','will filter out an entry');
$cfg['example']['filter']['link']['starts']       = array('words','that','will filter out an entry');
$cfg['example']['filter']['link']['contains']     = array('words','that','will filter out an entry');
$cfg['example']['filter']['link']['ends']         = array('words','that','will filter out an entry');
$cfg['example']['filter']['link']['regex']        = array('words','that','will filter out an entry');
$cfg['example']['filter']['content']['starts']    = array('words','that','will filter out an entry');
$cfg['example']['filter']['content']['contains']  = array('words','that','will filter out an entry');
$cfg['example']['filter']['content']['ends']      = array('words','that','will filter out an entry');
$cfg['example']['filter']['content']['regex']     = array('words','that','will filter out an entry');
$cfg['example']['filter']['author']['starts']     = array('words','that','will filter out an entry');
$cfg['example']['filter']['author']['contains']   = array('words','that','will filter out an entry');
$cfg['example']['filter']['author']['ends']       = array('words','that','will filter out an entry');
$cfg['example']['filter']['author']['regex']      = array('words','that','will filter out an entry');
$cfg['example']['filter']['category']['starts']   = array('words','that','will filter out an entry');
$cfg['example']['filter']['category']['contains'] = array('words','that','will filter out an entry');
$cfg['example']['filter']['category']['ends']     = array('words','that','will filter out an entry');
$cfg['example']['filter']['category']['regex']    = array('words','that','will filter out an entry');
*/

// Filter keep
/*
$cfg['example']['filter']['title']['starts']      = array('words','that','will keep an entry');
$cfg['example']['filter']['title']['contains']    = array('words','that','will keep an entry');
$cfg['example']['filter']['title']['ends']        = array('words','that','will keep an entry');
$cfg['example']['filter']['title']['regex']       = array('words','that','will keep an entry');
$cfg['example']['filter']['link']['starts']       = array('words','that','will keep an entry');
$cfg['example']['filter']['link']['contains']     = array('words','that','will keep an entry');
$cfg['example']['filter']['link']['ends']         = array('words','that','will keep an entry');
$cfg['example']['filter']['link']['regex']        = array('words','that','will keep an entry');
$cfg['example']['filter']['content']['starts']    = array('words','that','will keep an entry');
$cfg['example']['filter']['content']['contains']  = array('words','that','will keep an entry');
$cfg['example']['filter']['content']['ends']      = array('words','that','will keep an entry');
$cfg['example']['filter']['content']['regex']     = array('words','that','will keep an entry');
$cfg['example']['filter']['author']['starts']     = array('words','that','will keep an entry');
$cfg['example']['filter']['author']['contains']   = array('words','that','will keep an entry');
$cfg['example']['filter']['author']['ends']       = array('words','that','will keep an entry');
$cfg['example']['filter']['author']['regex']      = array('words','that','will keep an entry');
$cfg['example']['filter']['category']['starts']   = array('words','that','will keep an entry');
$cfg['example']['filter']['category']['contains'] = array('words','that','will keep an entry');
$cfg['example']['filter']['category']['ends']     = array('words','that','will keep an entry');
$cfg['example']['filter']['category']['regex']    = array('words','that','will keep an entry');
*/

/**
* NOTE: Global Filter Example
*
* Global filters, works as an antispam
* Skip any feed that contains the following keywords anywhere
*/
// Filter out
/*
$cfg['global_filter']['filter']['title']['starts']      = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['title']['contains']    = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['title']['ends']        = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['title']['regex']       = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['link']['starts']       = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['link']['contains']     = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['link']['ends']         = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['link']['regex']        = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['content']['starts']    = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['content']['contains']  = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['content']['ends']      = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['content']['regex']     = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['author']['starts']     = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['author']['contains']   = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['author']['ends']       = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['author']['regex']      = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['category']['starts']   = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['category']['contains'] = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['category']['ends']     = array('words','that','will filter out an entry');
$cfg['global_filter']['filter']['category']['regex']    = array('words','that','will filter out an entry');
*/
