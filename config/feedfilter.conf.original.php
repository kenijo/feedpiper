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
$feedfilter['feed_name']['url']   = [
  'http://github-trends.ryotarai.info/rss/github_trends_all_daily.rss',
];
// Whitelist: keep only entries matching the rules (executed before blacklisting)
// NOTE: At this time, only one whitelist can be used per feed (code needs to be reworked to handle multiple whitelists)
$feedfilter['feed_name']['whitelist']['author']['contains']   = ['keyword', 'keeps', 'entry'];
$feedfilter['feed_name']['whitelist']['category']['contains'] = ['keyword', 'keeps', 'entry'];
$feedfilter['feed_name']['whitelist']['content']['contains']  = ['keyword', 'keeps', 'entry'];
$feedfilter['feed_name']['whitelist']['title']['contains']    = ['keyword', 'keeps', 'entry'];
// Blacklist: exclude entries matching the rules (executed after whitelisting)
$feedfilter['feed_name']['blacklist']['author']['starts']     = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['author']['contains']   = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['author']['ends']       = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['author']['regex']      = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['category']['starts']   = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['category']['contains'] = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['category']['ends']     = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['category']['regex']    = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['content']['starts']    = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['content']['contains']  = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['content']['ends']      = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['content']['regex']     = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['title']['starts']      = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['title']['contains']    = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['title']['ends']        = ['keyword', 'excludes', 'entry'];
$feedfilter['feed_name']['blacklist']['title']['regex']       = ['keyword', 'excludes', 'entry'];

/**
* NOTE: Global Filter Example
*
* Global filters, works as an anti-spam
* Skip any feed that contains the following keywords
*/
// Blacklist: exclude entries matching the rule (no global whitelisting)
$feedfilter['global']['blacklist']['author']['starts']        = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['author']['contains']      = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['author']['ends']          = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['author']['regex']         = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['category']['starts']      = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['category']['contains']    = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['category']['ends']        = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['category']['regex']       = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['content']['starts']       = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['content']['contains']     = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['content']['ends']         = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['content']['regex']        = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['title']['starts']         = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['title']['contains']       = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['title']['ends']           = ['keyword', 'excludes', 'entry'];
$feedfilter['global']['blacklist']['title']['regex']          = ['keyword', 'excludes', 'entry'];
