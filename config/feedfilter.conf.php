<?php

/**
 * ATOM Filter Configuration
 *
 * Used to merge feeds together and output a single feed.
 * As well as filtering feed entries.
 */

/**
 * Use the following pattern
 * $cfg['example']['url']                                 = array('');
 * $cfg['example']['title']                               = ',';
 *
 * 'example' represent the name of the attribute being passed on in the URL
 * It can also be named 'global_filter' to work across all the feeds defined.
 * You can see it as an anti-spam for feeds
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
 *   http://domain/feedfilter.php?feed=example
 * You can debug the application by using
 *   http://domain/feedfilter.php?feed=example&debug=true
 */
$cfg['example']['title'] = 'Feedpiper Example - GitHub Trends';
$cfg['example']['url']   = array(
  'http://github-trends.ryotarai.info/rss/github_trends_all_daily.rss',
);
// Filter out
$cfg['example']['filter']['title']['starts']            = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['title']['contains']          = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['title']['ends']              = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['title']['regex']             = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link']['starts']             = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link']['contains']           = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link']['ends']               = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link']['regex']              = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link_original']['starts']    = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link_original']['contains']  = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link_original']['ends']      = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['link_original']['regex']     = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['content']['starts']          = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['content']['contains']        = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['content']['ends']            = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['content']['regex']           = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['author']['starts']           = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['author']['contains']         = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['author']['ends']             = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['author']['regex']            = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['category']['starts']         = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['category']['contains']       = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['category']['ends']           = array('words', 'that', 'will filter out an entry');
$cfg['example']['filter']['category']['regex']          = array('words', 'that', 'will filter out an entry');

// Filter keep
$cfg['example']['filter']['keep']['title']['starts']            = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['title']['contains']          = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['title']['ends']              = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['title']['regex']             = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link']['starts']             = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link']['contains']           = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link']['ends']               = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link']['regex']              = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link_original']['starts']    = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link_original']['contains']  = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link_original']['ends']      = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['link_original']['regex']     = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['content']['starts']          = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['content']['contains']        = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['content']['ends']            = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['content']['regex']           = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['author']['starts']           = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['author']['contains']         = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['author']['ends']             = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['author']['regex']            = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['category']['starts']         = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['category']['contains']       = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['category']['ends']           = array('words', 'that', 'will keep an entry');
$cfg['example']['filter']['keep']['category']['regex']          = array('words', 'that', 'will keep an entry');

/**
 * NOTE: Global Filter Example
 *
 * Global filters, works as an anti-spam
 * Skip any feed that contains the following keywords anywhere
 */
// Filter out
$cfg['global_filter']['filter']['title']['starts']            = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['title']['contains']          = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['title']['ends']              = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['title']['regex']             = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link']['starts']             = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link']['contains']           = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link']['ends']               = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link']['regex']              = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link_original']['starts']    = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link_original']['contains']  = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link_original']['ends']      = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['link_original']['regex']     = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['content']['starts']          = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['content']['contains']        = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['content']['ends']            = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['content']['regex']           = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['author']['starts']           = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['author']['contains']         = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['author']['ends']             = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['author']['regex']            = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['category']['starts']         = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['category']['contains']       = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['category']['ends']           = array('words', 'that', 'will filter out an entry');
$cfg['global_filter']['filter']['category']['regex']          = array('words', 'that', 'will filter out an entry');
