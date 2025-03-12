<?php

/**
 * Force SimplePie to use fsockopen() instead of cURL
 * If cURL doesn't work, set variable to false to use fsockopen()
 * Default value is true
 */
$useCurl = true;

/**
 * ATOM Filter Configuration
 *
 * Used to merge feeds together and output a single feed.
 * As well as filtering feed entries.
 *
 * Fields that can be filtered:
 *   title
 *   content
 *   author
 *   category
 *
 * Filtering matches the following expressions:
 *   starts   : equivalent to regex ^(.*)
 *   contains : equivalent to regex \b(.*)\b
 *   ends     : equivalent to regex (.*)$
 *   regex    : any regex
 *
 * NOTE: To use this configuration, call it with:
 *   https://DOMAIN/feedfilter.php?feed=feed_name
 *
 * Debug mode:
 *   https://DOMAIN/feedfilter.php?feed=feed_name&debug=true
 *   https://DOMAIN/feedfilter.php?feed=feed_name&debug=true&entry=1
 */

$feedConf = [
    // Global filters, works as an anti-spam
    // Skip any feed that contains the following keywords
    // Blacklist: exclude entries matching the rule (no global whitelist)
    'globalBlacklist' => [
        'author' => [
            'starts' => ['keyword', 'excludes', 'entry'],
            'contains' => ['keyword', 'excludes', 'entry'],
            'ends' => ['keyword', 'excludes', 'entry'],
            'regex' => ['keyword', 'excludes', 'entry'],
        ],
        'category' => [
            'starts' => ['keyword', 'excludes', 'entry'],
            'contains' => ['keyword', 'excludes', 'entry'],
            'ends' => ['keyword', 'excludes', 'entry'],
            'regex' => ['keyword', 'excludes', 'entry'],
        ],
        'content' => [
            'starts' => ['keyword', 'excludes', 'entry'],
            'contains' => ['keyword', 'excludes', 'entry'],
            'ends' => ['keyword', 'excludes', 'entry'],
            'regex' => ['keyword', 'excludes', 'entry'],
        ],
        'title' => [
            'starts' => ['keyword', 'excludes', 'entry'],
            'contains' => ['keyword', 'excludes', 'entry'],
            'ends' => ['keyword', 'excludes', 'entry'],
            'regex' => ['keyword', 'excludes', 'entry'],
        ],
    ],

    'feed_name' => [
        'title' => 'Feedpiper Example - GitHub Trends',
        'url' => [
            'http://github-trends.ryotarai.info/rss/github_trends_all_daily.rss',
        ],
        // Whitelist: never exclude an entry matching the rules (executed before blacklisting)
        'whitelist' => [
            'author' => ['contains' => ['keyword', 'keeps', 'entry']],
            'category' => ['contains' => ['keyword', 'keeps', 'entry']],
            'content' => ['contains' => ['keyword', 'keeps', 'entry']],
            'title' => ['contains' => ['keyword', 'keeps', 'entry']],
        ],
        // Blacklist: always exclude an entry matching the rules (executed after whitelisting if no whitelist match was found)
        'blacklist' => [
            'author' => [
                'starts' => ['keyword', 'excludes', 'entry'],
                'contains' => ['keyword', 'excludes', 'entry'],
                'ends' => ['keyword', 'excludes', 'entry'],
                'regex' => ['keyword', 'excludes', 'entry'],
            ],
            'category' => [
                'starts' => ['keyword', 'excludes', 'entry'],
                'contains' => ['keyword', 'excludes', 'entry'],
                'ends' => ['keyword', 'excludes', 'entry'],
                'regex' => ['keyword', 'excludes', 'entry'],
            ],
            'content' => [
                'starts' => ['keyword', 'excludes', 'entry'],
                'contains' => ['keyword', 'excludes', 'entry'],
                'ends' => ['keyword', 'excludes', 'entry'],
                'regex' => ['keyword', 'excludes', 'entry'],
            ],
            'title' => [
                'starts' => ['keyword', 'excludes', 'entry'],
                'contains' => ['keyword', 'excludes', 'entry'],
                'ends' => ['keyword', 'excludes', 'entry'],
                'regex' => ['keyword', 'excludes', 'entry'],
            ],
        ],
    ],
];
