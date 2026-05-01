<?php
/**
 * FeedPiper - Feed Merger and Filter
 *
 * This script processes RSS feeds according to configuration,
 * merges multiple feeds, and applies filtering rules.
 *
 * URL Parameters:
 * - feed: Required. The name of the feed configuration to use.
 * - debug: Optional. Set to true to enable debug mode.
 * - entry: Optional. Used with debug to specify which entry to examine.
 */

// Enable debug mode and error reporting if debug parameter is provided
if ($paramDebug = filter_input(INPUT_GET, 'debug', FILTER_VALIDATE_BOOL)) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);

    // Get specific entry number for debugging, defaults to 0
    $paramDebugEntry = filter_input(INPUT_GET, 'entry', FILTER_VALIDATE_INT) ?? 0;
}

// Include required libraries and dependencies
require_once __DIR__ . '/library/includes.php';

// Validate that a feed parameter was provided
if (!$paramFeedName = filter_input(INPUT_GET, 'feed', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
    echo 'Provide a "feed" parameter.';
    return;
}

// Check if the requested feed exists in configuration
if (!isset($feedConf[$paramFeedName])) {
    echo 'Configuration not found for feed: ' . $paramFeedName;
    return;
}

// Get the specific feed configuration
$feedConfig = $feedConf[$paramFeedName];

try {
    // Set up cache location for SimplePie
    $location = BASE_PATH . 'cache';

    // Initialize our PSR-16 compatible cache
    $cache = new \FeedPiper\Cache\FileCache($location, 3600);

    // Get the feed URLs, ensuring it's an array
    $urls = is_array($feedConfig['url']) ? $feedConfig['url'] : [$feedConfig['url']];
    $simplePieInstances = [];

    // Create one SimplePie instance per feed
    foreach ($urls as $url) {
        $simplePie = new \SimplePie\SimplePie();
        $simplePie->set_cache($cache);
        $simplePie->set_feed_url($url);
        $simplePie->init();
        $simplePie->set_output_encoding('utf-8');
        $simplePieInstances[] = $simplePie;
    }

    // Merge items from all instances
    $mergedItems = \SimplePie\SimplePie::merge_items($simplePieInstances);

} catch (Exception $e) {
    echo 'Failed to initialize SimplePie: ' . $e->getMessage();
}

// Create new feed object
$feed = new Feed();
$feed->setTitle($feedConfig['title']);
$feed->setDescription($feedConfig['title']);
$feed->setLink(is_array($feedConfig['url']) ? $feedConfig['url'] : [$feedConfig['url']]);
$feed->setCacheLocation($location);

// Process blacklist configuration
// Merge feed-specific blacklist with global blacklist if they exist
$blacklist = isset($feedConfig['blacklist']) ? $feedConfig['blacklist'] : [];
$globalBlacklist = isset($feedConf['globalBlacklist']) ? $feedConf['globalBlacklist'] : [];
$mergedBlacklists = mergeArrays($blacklist, $globalBlacklist);
if (isset($mergedBlacklists)) {
    $blacklist = cleanArray($mergedBlacklists, 'strtolower');
}

// Process whitelist configuration
$whitelist = isset($feedConfig['whitelist']) ? $feedConfig['whitelist'] : [];
$whitelist = cleanArray($whitelist, 'strtolower');

// Open feed - either in debug or normal mode
if ($paramDebug) {
    // Debug mode outputs plain text
    header('Content-Type: text/plain; charset=utf-8');
    $feed->printFormatDebug('Debug Mode', 'Entry ' . $paramDebugEntry);
    $feed->printFormatDebug('Feed URL: ', 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    echo PHP_EOL;
    $feed->printOpenDebug();
    // Only select the specific entry for debugging
    $simplePieItems = [$mergedItems[$paramDebugEntry]];
} else {
    // Normal mode outputs XML
    //header('Content-Type: application/rss+xml; charset=utf-8');
    header('Content-Type: application/xml; charset=utf-8');
    $feed->printOpen();
    $simplePieItems = $mergedItems;
}

// Process entries in batches of 50 to manage memory usage
$SimplePieBatch = array_chunk($simplePieItems, 50);

$itemList = [];
foreach ($SimplePieBatch as $items) {
    foreach ($items as $item) {
        // Create a new feed entry for each item
        $feedEntry = new FeedEntry();
        $feedEntry->setTitle($item->get_title());
        $feedEntry->setLink($item->get_link());
        $feedEntry->setId($item->get_id());
        $feedEntry->setPubDate($item->get_date('D, d M Y H:i:s'));

        // Process description and content
        $description = $item->get_description();
        $content = $item->get_content();

        // Check for body tag and use it as fallback for description/content
        $bodyTags = $item->get_item_tags('http://www.w3.org/1999/xhtml', 'body');
        if ($bodyTags) {
            $body = $bodyTags[0]['data'];
            if (!$description) {
                $description = $body;
            }
            if (!$content) {
                $content = $body;
            }
        }

        // If description and content are identical, don't duplicate content
        if ($description == $content) {
            $content = '';
        }
        // TODO: Do I need cleanEntryContent()? If so, make cleanEntryContent() part of class.feedentry.php
        $feedEntry->setDescription($description);
        $feedEntry->setContent($content);

        // Set metadata for the entry
        $feedEntry->setAuthors($item->get_authors(), $item->get_contributors());
        $feedEntry->setCategories($item->get_categories());

        // Handle media enclosures and thumbnails
        // Try to find a thumbnail if no enclosure is present
        if (!$item->get_enclosure()->get_link()) {
            if ($thumbnail = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail')) {
                $item->get_enclosure()->link = $thumbnail[0]['attribs']['']['url'];
            } elseif ($thumbnail = $item->get_item_tags('http://base.google.com/ns/1.0', 'image_link')) {
                $item->get_enclosure()->link = $thumbnail[0]['data'];
            }
        }
        $feedEntry->setEnclosure($item->get_enclosure());

        // Apply filtering rules
        $feedEntry->setBlacklist($blacklist);
        $feedEntry->setWhitelist($whitelist);

        // Filter entries based on configured rules
        $feedEntry->filterEntries();

        if (empty($feedEntry->getTitle())) {
            $feedEntry->setSkip(true);
        }

        if (empty($feedEntry->getDescription())) {
            $feedEntry->setSkip(true);
        }

        // Print feed entry
        if ($paramDebug) {
            // In debug mode, print detailed information about the entry and exit
            $feedEntry->printDebug();
            exit;
        } elseif ($feedEntry->getSkip()) {
            // Skip entries that don't pass filters
            continue;
        } else {
            // In normal mode, add the entry to the feed
            $feedEntry->print();
        }

        // Clean up to free memory
        unset($feedEntry);
    }

    // Run garbage collection after each batch to manage memory
    gc_collect_cycles();
}

// Close the feed in normal mode
if (!$paramDebug) {
    $feed->printClose();
}

/**
 * Function to clean content and description
 *
 * Removes excessive whitespace and performs other cleanup on entry content
 *
 * @param string $entry The entry content or description
 * @return string The cleaned content or description
 */
function cleanEntryContent(string $entry): string
{
    $patterns = [
        '#(\s)+#imu' => ' ',    // Remove left over spaces
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
function mergeArrays(array $array1, array $array2): array
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
