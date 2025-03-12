<?php

/**
 * Force SimplePie to use fsockopen() instead of cURL
 * If cURL doesn't work, set variable to false to use fsockopen()
 * Default value is true
 */
$useCurl = true;

/**
 * HTML2FEED Configuration
 * Used to turn a page into a feed.
 *
 * Configure the application below and call it with
 *   https://DOMAIN/html2feed.php?page=feed_name
 * You can debug the application by using
 *   https://DOMAIN/html2feed.php?page=feed_name&debug=true
 */
$html2feed = [
    'feed' => [
        'page_title'  => 'Another Feed Example',
        'page_url'    => 'https://example.com/',
        'entry'       => 'div.entry',
        'title'       => 'h2.title',
        'description' => 'div.summary',
        'link'        => 'a.read-more',
        'author'      => 'span.author',
        'category'    => 'span.category',
        'thumbnail'   => 'img.thumbnail',
    ],
];
