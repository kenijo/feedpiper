<?php

// Include libraries
require_once __DIR__ . '/library/inc.lib.php';

if (isset($_GET['feed'])) {
  $feed = $_GET['feed'];

  if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    $debug = true;
  } else {
    $debug = false;
  }


  if (isset($_GET['error']) && $_GET['error'] === 'true') {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
  }

  if (isset($feedfilter[$feed])) {
    $feedConfig = $feedfilter[$feed];
  } else {
    echo 'A configuration could not be found in the configuration file for: ' . $feed;
    return;
  }

  $i = 1;
  $feedList = [];
  foreach ($feedConfig['url'] as $feedUrl) {
  // Create a new instance of SimplePie
    ${$feed . '_' . $i} = new \SimplePie\SimplePie();

  // Set the length of time(in seconds) that the contents of a feed will be cached
  if (isset($cfg['cache_length'])) {
      ${$feed . '_' . $i}->set_cache_duration($cfg['cache_length']);
  }

  // Set MySQL caching
  // Otherwise we will use file caching (cache folder must be system writable)
  if (isset($cfg['mysql'])) {
    $location = 'mysql://' . $cfg['mysql']['user'] . ':' . $cfg['mysql']['password'] . '@' . $cfg['mysql']['host'] . ':' . $cfg['mysql']['port'] . '/' . $cfg['mysql']['database'];
  } else {
    $location = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['REQUEST_URI']) . '/cache';
    if (!file_exists($location)) {
      mkdir($location, 0777, true);
    }
  }
  ${$feed . '_' . $i}->set_cache_location($location);

  // Force SimplePie to use fsockopen() instead of cURL
  if (isset($cfg['curl']) && $cfg['curl'] === false) {
      ${$feed . '_' . $i}->force_fsockopen(true);
  }

  if (!isset($feedConfig['title'])) {
    echo 'A "title" could not be found in the configuration file for: ' . $feed;
    return;
  }

  // Set the URL of the feed(s) you want to parse
    if (isset($feedUrl)) {
      ${$feed . '_' . $i}->set_feed_url($feedUrl);
  } else {
    echo 'A "url" could not be found in the configuration file for: ' . $feed;
    return;
  }

  // Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and
  // all that other good stuff.  The feed's information will not be available to SimplePie before
  // this is called.
    $success = ${$feed . '_' . $i}->init();

  // We'll make sure that the right content type and character encoding gets set automatically.
  // This function will grab the proper character encoding, as well as set the content type to text/html.
  ${$feed . '_' . $i}->handle_content_type();

  // Send the content-type header with correct encoding
  if ($debug === true) {
    $contentType = 'text/plain';
  } elseif (isset($cfg['feed_format']) && $cfg['feed_format'] == 'ATOM') {
    $contentType = 'application/atom+xml';
  } elseif (isset($cfg['feed_format']) && $cfg['feed_format'] == 'RSS') {
    $contentType = 'application/rss+xml';
  } else {
    $contentType = 'application/xml';
  }
  header('Content-type: ' . $contentType . '; charset=utf-8');

  // Check to see if there are more than zero errors (i.e. if there are any errors at all)
    if (${$feed . '_' . $i}->error()) {
      echo htmlspecialchars(${$feed . '_' . $i}->error()[0]);
  }

    $feedList[] = ${$feed . '_' . $i};
    $i++;
  }

  // Merge all the feeds together
  $mergedFeeds = \SimplePie\SimplePie::merge_items($feedList);

  // SimplePie API Documentation
  // http://simplepie.org/api/class-SimplePie_Item.html
  // Initialize the SimplePie feed object
  if ($success) {
    // Use GMT as the default time zone.
    $date = new DateTime('now', new DateTimezone('GMT'));

    $newFilteredFeed = new Filter($cfg['feed_format']);
    $newFeed         = new Feed($cfg['feed_format']);

    // Cleanup the filters
    if (isset($feedConfig['whitelist'])) {
      $newFilteredFeed->setWhitelist(cleanArray($feedConfig['whitelist'], 'strtolower'));
    }

    if (isset($feedConfig['blacklist'])) {
      $newFilteredFeed->setBlacklist(cleanArray($feedConfig['blacklist'], 'strtolower'));
    }

    // Cleanup the global filters
    if (isset($feedfilter['global']['blacklist'])) {
      $newFilteredFeed->setGlobalBlacklist(cleanArray($feedfilter['global']['blacklist'], 'strtolower'));
    }

    $newFeed->setGeneratorName(\SimplePie\SimplePie::NAME);
    $newFeed->setGeneratorUri($_SERVER['REQUEST_URI']);
    $newFeed->setGeneratorVersion(\SimplePie\SimplePie::VERSION);
    $newFeed->setIcon(urlDirPath() . '/favicon.ico');
    $newFeed->setId(urlFilePath());
    $newFeed->setLink(urlFilePath());
    $newFeed->setLinkAlternate($feedConfig['url'][0]);
    $newFeed->setLogo(urlDirPath() . '/favicon.png');
    $newFeed->setTitle($feedConfig['title']);
    if ($cfg['feed_format'] == 'ATOM') {
      $date_format = DATE_ATOM;
    } elseif ($cfg['feed_format'] == 'RSS') {
      $date_format = DATE_RSS;
    }
    $newFeed->setUpdated($date->format($date_format));

    $parsed_url = parse_url($feedConfig['url'][0]);
    $website_link = $parsed_url['scheme'] . '://' . $parsed_url['host'];
    $newFeed->setWebsiteLink($website_link);

    // Display or Debug feed
    if ($debug === true) {
      $newFeed->debug();
    } else {
      $newFeed->open();
    }

    // Create an array of unique identifiers to skip duplicate entries
    $identifier_list = [];

    foreach ($mergedFeeds as $entry) {
      $newEntry = new Entry($cfg['feed_format']);

      // Select a specific entry for debug purpose
      if ($debug === true) {
        if (isset($_GET['entry'])) {
          $e = $_GET['entry'];
        } else {
          $e = 0;
        }
        $array = $mergedFeeds;
        $newFilteredFeed->filter($array[$e]);
      } else {
        $newFilteredFeed->filter($entry);
      }

      // Set Id
      $newEntry->setId($newFilteredFeed->getId());

      // Set Link
      $nff = $newFilteredFeed->getLink();
      $newEntry->setLink($nff);

      // Set Identifier
      //$identifier = basename($newFilteredFeed->get_id()) . '@' . parse_url($newFilteredFeed->get_link())['host'];
      //$identifier ? $identifier : $identifier = null;
      //$newEntry->set_entry_identifier($identifier);
      $newEntry->setIdentifier($newFilteredFeed->getLink());

      // Check if the identifier of the entry already exists
      // If it already exists then we skip it(remove duplicates)
      if ((in_array($newEntry->getIdentifier(), $identifier_list) === false && $newFilteredFeed->getSkip() !== true) || $debug === true) {
        // Set Published Date
        $newEntry->setPublished($newFilteredFeed->getDate());

        // Set Updated Date
        $newEntry->setUpdated($newFilteredFeed->getDate());

        // Set Title
        $title = $newFilteredFeed->getTitle();
        $newEntry->setTitle($title);

        // Set Enclosure
        $newEntry->setEnclosureLength($newFilteredFeed->getEnclosureLength());
        $newEntry->setEnclosureLink($newFilteredFeed->getEnclosureLink());
        $newEntry->setEnclosureType($newFilteredFeed->getEnclosureType());

        // Set Authors
        $newEntry->setAuthors($newFilteredFeed->getAuthors());

        // Set Categories
        $newEntry->setCategories($newFilteredFeed->getCategories());

        // Set Summary
        if ($summary = $newFilteredFeed->getSummary()) {
          // Insert Authors and Categories into it
          $summary .= '        <br />' . PHP_EOL;
          if ($newFilteredFeed->getAuthors()) {
            $summary .= '        <br />Author(s) : ' . convertArrayToCommaSeparatedString($newEntry->getAuthors()) . PHP_EOL;
          }
          if ($newFilteredFeed->getCategories()) {
            $summary .= '        <br />Categories: ' . convertArrayToCommaSeparatedString($newEntry->getCategories()) . PHP_EOL;
          }
        }
        $newEntry->setSummary($summary);

        // Set Content
        if ($content = $newFilteredFeed->getContent()) {
          // Modify content here as needed
        }
        $newEntry->setContent($content);

        // Display debug view
        if ($debug === true) {
          $newEntry->debug();
          $newFilteredFeed->debug();

          unset($newFilteredFeed);
          unset($newEntry);
          unset($newFeed);

          return;
        } else {
          $newEntry->add();
        }
        // Add the entry identifier to the identifier_list
        array_push($identifier_list, $newEntry->getIdentifier());
      }
      unset($newEntry);
    }
    $newFeed->close();
  }
  unset($newFilteredFeed);
  unset($newFeed);
} else {
  echo 'Please provide a "feed" parameter.';
}
