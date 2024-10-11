<?php

/**
* ATOM Filter Configuration
*
* Used to merge feeds together and output a single feed.
* As well as filtering feed entries.
*/

/**
*   title
*   content
*   author
*   category
*
*   Filtering matches the following expressions:
*     starts   : equivalent to regex ^(.*)
*     contains : equivalent to regex \b(.*)\b
*     ends     : equivalent to regex (.*)$
*     regex    : any regex
*/

/**
* NOTE: Example with GitHub Trends
*
* Configure the application below and call it with
*   https://DOMAIN/feedfilter.php?feed=feed_name
* You can debug the application by using
*   https://DOMAIN/feedfilter.php?feed=feed_name&debug=true
*   https://DOMAIN/feedfilter.php?feed=feed_name&debug=true&entry=1
*/
$feedfilter['feed_name']['title'] = 'Feedpiper Example - GitHub Trends';
$feedfilter['feed_name']['url']   = array(
  'http://github-trends.ryotarai.info/rss/github_trends_all_daily.rss',
);
// Whitelist: keep only entries matching the rules (executed before blacklisting)
// NOTE: At this time, only one whitelist can be used per feed (code needs to be reworked to handle multiple whitelists)
$feedfilter['feed_name']['whitelist']['author']['contains']   = array('keyword', 'keeps', 'entry');
$feedfilter['feed_name']['whitelist']['category']['contains'] = array('keyword', 'keeps', 'entry');
$feedfilter['feed_name']['whitelist']['content']['contains']  = array('keyword', 'keeps', 'entry');
$feedfilter['feed_name']['whitelist']['title']['contains']    = array('keyword', 'keeps', 'entry');
// Blacklist: exclude entries matching the rules (executed after whitelisting)
$feedfilter['feed_name']['blacklist']['author']['starts']     = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['author']['contains']   = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['author']['ends']       = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['author']['regex']      = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['category']['starts']   = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['category']['contains'] = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['category']['ends']     = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['category']['regex']    = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['content']['starts']    = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['content']['contains']  = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['content']['ends']      = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['content']['regex']     = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['title']['starts']      = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['title']['contains']    = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['title']['ends']        = array('keyword', 'excludes', 'entry');
$feedfilter['feed_name']['blacklist']['title']['regex']       = array('keyword', 'excludes', 'entry');

/**
* NOTE: Global Filter Example
*
* Global filters, works as an anti-spam
* Skip any feed that contains the following keywords
*/
// Blacklist: exclude entries matching the rule (no global whitelisting)
$feedfilter['global']['blacklist']['author']['starts']        = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['author']['contains']      = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['author']['ends']          = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['author']['regex']         = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['category']['starts']      = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['category']['contains']    = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['category']['ends']        = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['category']['regex']       = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['content']['starts']       = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['content']['contains']     = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['content']['ends']         = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['content']['regex']        = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['title']['starts']         = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['title']['contains']       = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['title']['ends']           = array('keyword', 'excludes', 'entry');
$feedfilter['global']['blacklist']['title']['regex']          = array('keyword', 'excludes', 'entry');
