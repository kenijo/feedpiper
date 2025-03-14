<?php

// Enable debug mode and error reporting
if ($paramDebug = filter_input(INPUT_GET, 'debug', FILTER_VALIDATE_BOOLEAN)) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);

    $paramDebugEntry = filter_input(INPUT_GET, 'entry', FILTER_VALIDATE_INT);
}

// Include libraries
require_once __DIR__ . '/library/includes.php';

if (!$paramFeedName = filter_input(INPUT_GET, 'feed', FILTER_SANITIZE_STRING)) {
    echo 'Please provide a "feed" parameter.';
    return;
}

if (!isset($feedConf[$paramFeedName])) {
    echo 'Configuration not found for: ' . $paramFeedName;
    return;
}

// Create a list of feeds and initialize them for a given feedId
$simplePieFeedList = [];
$feed = $feedConf[$paramFeedName];
foreach ($feed['url'] as $feedUrl) {
    $simplePieFeed = initializeFeed($paramFeedName, $feedUrl, $useCurl);
    if ($simplePieFeed) {
        $simplePieFeedList[] = $simplePieFeed;
    } else {
        echo 'Failed to initialize feed for URL: ' . $feedUrl;
    }
}

// Merge all feeds
$simplePieMergedItems = SimplePie::merge_items($simplePieFeedList);

// Initialize new feed
$newFeed = new FeedFilter($simplePieFeedList[0]);
$newFeed->setFeedTitle($feed['title']);

// Open feed
if ($paramDebug) {
    header('Content-type: text/plain; charset=utf-8');
    $newFeed->debugPrint('Debug Mode:', $paramDebug);
    $newFeed->debugPrint('Debug Entry: ', $paramDebugEntry);
    $newFeed->debugPrint('Feed URL: ', 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    echo PHP_EOL;
    $newFeed->debugFeed();
} else {
    // header('Content-type: application/rss+xml; charset=utf-8');
    header('Content-type: application/xml; charset=utf-8');
    $newFeed->openFeed();
}

// Set whitelist and blacklist filters
if (isset($feed['whitelist'])) {
    $whitelist = cleanArray($feed['whitelist'], 'strtolower');
    $newFeed->setFeedWhitelist($whitelist);
}

if (isset($feed['blacklist'])) {
    $globalBlacklist = isset($feedConf['globalBlacklist']) ? $feedConf['globalBlacklist'] : [];
    $blacklist = mergeArrays($feed['blacklist'], $globalBlacklist);
    $blacklist = cleanArray($blacklist, 'strtolower');
    $newFeed->setFeedBlacklist($blacklist);
}

// Process entries in batches
const BATCH_SIZE = 50;
$entries = array_chunk($simplePieMergedItems, BATCH_SIZE);

$feedEntryListById = [];
foreach ($entries as $batch) {
    foreach ($batch as $entry) {
        // Add a new (filtered) feed entry
        $newFeed->setFeedEntrySkip(false);

        // Track existing entries and skip duplicate ones
        if (in_array($entry->get_id(true), $feedEntryListById)) {
            continue;
        } else {
            $feedEntryListById[] = $entry->get_id(true);
        }

        $entry = $paramDebugEntry ? $simplePieMergedItems[$paramDebugEntry] : $entry;
        $newFeed->setFeedEntryTitle($entry->get_title());

        $description = $entry->get_description() ?: null;
        $newFeed->setFeedEntryDescription($entry->get_description());
        if ($entry->get_item_tags('', 'body')) {
            $content = $entry->get_item_tags('', 'body')[0]['data'];
        } elseif ($entry->get_content() != $entry->get_description()) {
            $content = $entry->get_content();
        } else {
            $content = null;
        }

        $description = cleanEntryContent($description);
        $content = cleanEntryContent($content);

        $newFeed->setFeedEntryDescription($description);
        $newFeed->setFeedEntryContent($content);

        $newFeed->setFeedEntryLink($entry->get_link());
        $newFeed->setfeedEntryId($entry->get_id(true));
        $newFeed->setFeedEntryPubDate($entry->get_date(DATE_RSS));

        // Create a sorted unique list of authors by combining Authors and Contributors
        $authors = [];
        if ($entry->get_authors()) {
            foreach ($entry->get_authors() as $author) {
                if ($author->get_email()) {
                    $authors[] = $author->get_email();
                }
                if ($author->get_name()) {
                    $authors[] = $author->get_name();
                }
            }
        }
        if ($entry->get_contributors()) {
            foreach ($entry->get_contributors() as $contributor) {
                if ($contributor->get_email()) {
                    $authors[] = $contributor->get_email();
                }
                if ($contributor->get_name()) {
                    $authors[] = $contributor->get_name();
                }
            }
        }
        $authors = cleanArray($authors);

        if ($authors) {
            $newFeed->setFeedEntryAuthors($authors);
        } else {
            $newFeed->setFeedEntryAuthors(null);
        }

        // Create a sorted unique list of categories
        $categories = [];
        if ($entry->get_categories()) {
            foreach ($entry->get_categories() as $category) {
                if ($category->get_label()) {
                    $categories[] = $category->get_label();
                }
                if ($category->get_term()) {
                    $categories[] = $category->get_term();
                }
            }
        }

        // If we don't have categories defined then generate some based on the link and title
        if (!$categories) {
            // NOTE START - Uncomment this section if you want to generate categories from links
            if ($link = $entry->get_link()) {
                $link = urldecode($link);
                $link = substr($link, strpos($link, '//') + 2);
                $link = substr($link, strpos($link, '/') + 1);
                $link = substr($link, 0, strrpos($link, '/'));
                $link = parse_url($link)['path'];
                $link = preg_replace('#' . '[[:punct:]]' . '#imu', ' ', $link);
                $categories = explode(' ', $link);
            }
            // NOTE END - Uncomment this section if you want to generate categories from links

            // NOTE START - Uncomment this section if you want to generate categories from titles
            /*
            if ($title = $this->getFeedEntryTitle())
            {
              $title = html_entity_decode($title);
              $title = strip_tags($title);
              $title = remove_accents($title);

              $colon    = before(':', $title);
              $dot      = before('.', $title);
              $bracket  = between('[', ']', $title);
              $curly    = between('{', '}', $title);

              $title    =  $colon . ' ' . $dot . ' ' . $bracket . ' ' . $curly;
              $title    = preg_replace('#'.'[[:punct:]]'.'#imu', ' ', $title);

              $categories = explode(' ', $title);
            }
            */
            // NOTE END - Uncomment this section if you want to generate categories from titles

            // Get rid of categories that are null, empty, false, < 3 and numbers only
            $categories = array_filter($categories, function ($key) {
                return strlen($key) > 3 && !preg_match('#' . '\d+' . '#imu', $key);
            });
        }
        // Remove categories with a pipe (|) (i.e. engadget feeds )
        $categories = array_filter($categories, fn($item) => strpos($item, '|') === false);
        $categories = cleanArray($categories, 'ucwords');

        if ($categories) {
            $newFeed->setFeedEntryCategories($categories);
        } else {
            $newFeed->setFeedEntryCategories(null);
        }

        // Set media link based on enclosure, media:thumbnail, or image_link
        if (!$entry->get_enclosure()->get_link()) {
            if ($thumbnail = $entry->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail')) {
                $entry->get_enclosure()->link = $thumbnail[0]['attribs']['']['url'];
                $newFeed->setFeedEntryEnclosure($entry->get_enclosure());
            } elseif ($thumbnail = $entry->get_item_tags('http://base.google.com/ns/1.0', 'image_link')) {
                $entry->get_enclosure()->link = $thumbnail[0]['data'];
                $newFeed->setFeedEntryEnclosure($entry->get_enclosure());
            }
        } else {
            $newFeed->setFeedEntryEnclosure($entry->get_enclosure());
        }

        $entries = [
            'author' => $newFeed->getFeedEntryAuthors(),
            'category' => $newFeed->getFeedEntryCategories(),
            'content' => $newFeed->getFeedEntryContent() ?: $newFeed->getFeedEntryDescription(),
            'title' => $newFeed->getFeedEntryTitle()
        ];
        $newFeed->filterEntries($entries);

        // Add feed entry
        if ($paramDebug) {
            $newFeed->debugFeedEntry();
            break 2;
        } elseif ($newFeed->getFeedEntrySkip()) {
            continue;
        } else {
            $newFeed->addFeedEntry();
        }
    }

    // After processing each batch
    gc_collect_cycles();
}

// Close feed
if (!$paramDebug) {
    $newFeed->closeFeed();
}

/**
 * Function to initialize SimplePie feed
 *
 * @param string $feedName The name of the feed
 * @param string $feedUrl The URL of the feed
 * @param bool $useCurl Whether to use cURL or not
 * @return SimplePie|null The initialized SimplePie object or null if an error occurred
 */
function initializeFeed($feedName, $feedUrl, $useCurl)
{
    $simplePie = new SimplePie();

    $simplePie->set_cache_duration(259200); // 3 days

    // Force SimplePie to use fsockopen() instead of cURL if configured
    if (isset($useCurl) && $useCurl === false) {
        $simplePie->force_fsockopen(true);
    }

    // Set cache location
    $location = BASE_PATH . 'cache';
    if (!file_exists($location)) {
        mkdir($location, 0777, true);
    }
    $simplePie->set_cache_location($location);

    // Set feed URL
    $simplePie->set_feed_url($feedUrl);

    // Initialize SimplePie
    $simplePie->init();
    $simplePie->handle_content_type();

    return $simplePie->error() ? null : $simplePie;
}

/**
 * Function to clean content and description
 *
 * @param string $entry The entry content or description
 * @return string The cleaned content or description
 */
function cleanEntryContent($entry)
{
    $patterns = [
        '#<br clear="all">.*#imu' => '',    // Remove tracking links if present
        '#<div.*?></div>#imu' => '',        // Remove tracking links if present
        '#(\s)+#imu' => ' ',                // Remove left over spaces// Remove left over spaces
    ];

    foreach ($patterns as $pattern => $replacement) {
        $entry = trim(preg_replace($pattern, $replacement, $entry));
    }

    return $entry;
}

/**
 * Merges two arrays recursively (blacklist and globalBlacklist)
 *
 * This function takes two arrays and merges them into a single array.
 * If there are overlapping keys, the arrays are merged recursively.
 *
 * @param array $array1 The first array to merge.
 * @param array $array2 The second array to merge.
 * @return array The merged array.
 */
function mergeArrays($array1, $array2)
{
    $mergedArrays = $array2;

    foreach ($array1 as $key => $value) {
        if (isset($mergedArrays[$key]) && is_array($mergedArrays[$key]) && is_array($value)) {
            $mergedArrays[$key] = array_merge_recursive($mergedArrays[$key], $value);
        } else {
            $mergedArrays[$key] = $value;
        }
    }

    return $mergedArrays;
}
