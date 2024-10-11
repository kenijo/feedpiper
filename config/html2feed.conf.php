<?php

/**
 * HTML2FEED Configuration
 *
 * Used to turn a page into a feed.
 */

/**
 * NOTE: Example with GitHub Guides
 *
 * Configure the application below and call it with
 *   https://DOMAIN/html2feed.php?page=feed_name
 * You can debug the application by using
 *   https://DOMAIN/html2feed.php?page=feed_name&debug=true
 */
$html2feed['feed_name']['page_title']  = 'HTML2FEED Example - GitHub Guides';
$html2feed['feed_name']['page_url']    = 'https://guides.github.com/';
$html2feed['feed_name']['entry']       = 'article[class="guide-listing"]';
$html2feed['feed_name']['title']       = 'h3[class="guide-cover-title"]';
$html2feed['feed_name']['description'] = 'p[class="guide-summary"]';
$html2feed['feed_name']['link']        = 'a';
$html2feed['feed_name']['author']      = null;
$html2feed['feed_name']['category']    = null;
$html2feed['feed_name']['thumbnail']   = null;
