<?php

// Include configuration file
require_once 'include/inc.lib.php';

if (isset($_GET['feed'])) {
  $myFeed = $_GET['feed'];

  if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    $myFeedDebug = true;
  } else {
    $myFeedDebug = false;
  }

  if (isset($feedfilter[$myFeed])) {
    $myFeedConfig = $feedfilter[$myFeed];
  } else {
    echo 'A configuration could not be found in the configuration file for: ' . $myFeed;
    return;
  }

  $i = 1;
  $feed_array = array();
  foreach ($myFeedConfig['url'] as $feed_url) {
  // Create a new instance of SimplePie
    ${$myFeed . '_' . $i} = new \SimplePie\SimplePie();

  // Set the length of time(in seconds) that the contents of a feed will be cached
  if (isset($cfg['cache_length'])) {
      ${$myFeed . '_' . $i}->set_cache_duration($cfg['cache_length']);
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
    ${$myFeed . '_' . $i}->set_cache_location($location);

  // Force SimplePie to use fsockopen() instead of cURL
  if (isset($cfg['curl']) && $cfg['curl'] === false) {
      ${$myFeed . '_' . $i}->force_fsockopen(true);
  }

  if (!isset($myFeedConfig['title'])) {
    echo 'A "title" could not be found in the configuration file for: ' . $myFeed;
    return;
  }

  // Set the URL of the feed(s) you want to parse
    if (isset($feed_url)) {
      ${$myFeed . '_' . $i}->set_feed_url($feed_url);
  } else {
    echo 'A "url" could not be found in the configuration file for: ' . $myFeed;
    return;
  }

  // Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and
  // all that other good stuff.  The feed's information will not be available to SimplePie before
  // this is called.
    $success = ${$myFeed . '_' . $i}->init();

  // We'll make sure that the right content type and character encoding gets set automatically.
  // This function will grab the proper character encoding, as well as set the content type to text/html.
    ${$myFeed . '_' . $i}->handle_content_type();

  // Send the content-type header with correct encoding
  if ($myFeedDebug === true) {
    $content_type = 'text/plain';
  } elseif (isset($cfg['feed_format']) && $cfg['feed_format'] == 'ATOM') {
    $content_type = 'application/atom+xml';
  } elseif (isset($cfg['feed_format']) && $cfg['feed_format'] == 'RSS') {
    $content_type = 'application/rss+xml';
  } else {
    $content_type = 'application/xml';
  }
  header('Content-type: ' . $content_type . '; charset=utf-8');

  // Check to see if there are more than zero errors (i.e. if there are any errors at all)
    if (${$myFeed . '_' . $i}->error()) {
      echo htmlspecialchars(${$myFeed . '_' . $i}->error()[0]);
  }

    $feed_array[] = ${$myFeed . '_' . $i};
    $i++;
  }

  // Merge all the feeds together
  $mergedFeeds = \SimplePie\SimplePie::merge_items($feed_array);

  // SimplePie API Documentation
  // http://simplepie.org/api/class-SimplePie_Item.html
  // Initialize the SimplePie feed object
  if ($success) {
    // Use GMT as the default time zone.
    $date = new DateTime('now', new DateTimezone('GMT'));

    $newFilteredFeed = new FilteredFeed($cfg['feed_format']);
    $newFeed         = new Feed($cfg['feed_format']);

    // Cleanup the filters
    if (isset($myFeedConfig['whitelist'])) {
      $newFilteredFeed->set_entry_whitelist(cleanArray($myFeedConfig['whitelist'], 'strtolower'));
    }
    
    if (isset($myFeedConfig['blacklist'])) {
      $newFilteredFeed->set_entry_blacklist(cleanArray($myFeedConfig['blacklist'], 'strtolower'));
    }

    // Cleanup the global filters
    if (isset($feedfilter['global']['blacklist'])) {
      $newFilteredFeed->set_global_blacklist(cleanArray($feedfilter['global']['blacklist'], 'strtolower'));
    }

    $newFeed->set_feed_generator_name(\SimplePie\SimplePie::NAME);
    $newFeed->set_feed_generator_uri($_SERVER['REQUEST_URI']);
    $newFeed->set_feed_generator_version(\SimplePie\SimplePie::VERSION);
    $newFeed->set_feed_icon(url_dir_path() . '/favicon.ico');
    $newFeed->set_feed_id(url_file_path());
    $newFeed->set_feed_link(url_file_path());
    $newFeed->set_feed_link_alternate($myFeedConfig['url'][0]);
    $newFeed->set_feed_logo(url_dir_path() . '/favicon.png');
    $newFeed->set_feed_title($myFeedConfig['title']);
    if ($cfg['feed_format'] == 'ATOM') {
      $date_format = DATE_ATOM;
    } elseif ($cfg['feed_format'] == 'RSS') {
      $date_format = DATE_RSS;
    }
    $newFeed->set_feed_updated($date->format($date_format));

    $parsed_url = parse_url($myFeedConfig['url'][0]);
    $website_link = $parsed_url['scheme'] . '://' . $parsed_url['host'];
    $newFeed->set_feed_website_link($website_link);

    // Display or Debug feed
    if ($myFeedDebug === true) {
      $newFeed->debug_feed();
    } else {
      $newFeed->open_feed();
    }

    // Create an array of unique identifiers to skip duplicate entries
    $identifier_list = array();

    foreach ($mergedFeeds as $entry) {
      $newEntry = new Entry($cfg['feed_format']);

      // Select a specific entry for debug purpose
      if ($myFeedDebug === true) {
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
      $newEntry->set_entry_id($newFilteredFeed->get_id());

      // Set Link
      $nff = $newFilteredFeed->get_link();
      $newEntry->set_entry_link($nff);

      // Set Identifier
      //$identifier = basename($newFilteredFeed->get_id()) . '@' . parse_url($newFilteredFeed->get_link())['host'];
      //$identifier ? $identifier : $identifier = null;
      //$newEntry->set_entry_identifier($identifier);
      $newEntry->set_entry_identifier($newFilteredFeed->get_link());

      // Check if the identifier of the entry already exists
      // If it already exists then we skip it(remove duplicates)
      if ((in_array($newEntry->get_entry_identifier(), $identifier_list) === false && $newFilteredFeed->get_skip() !== true) || $myFeedDebug === true) {
        // Set Published Date
        $newEntry->set_entry_published($newFilteredFeed->get_date());

        // Set Updated Date
        $newEntry->set_entry_updated($newFilteredFeed->get_date());

        // Set Title
        $title = $newFilteredFeed->get_title();
        $newEntry->set_entry_title($title);

        // Set Enclosure
        $newEntry->set_entry_enclosure_length($newFilteredFeed->get_enclosure_length());
        $newEntry->set_entry_enclosure_link($newFilteredFeed->get_enclosure_link());
        $newEntry->set_entry_enclosure_type($newFilteredFeed->get_enclosure_type());

        // Set Authors
        $newEntry->set_entry_authors($newFilteredFeed->get_authors());

        // Set Categories
        $newEntry->set_entry_categories($newFilteredFeed->get_categories());

        // Set Summary
        if ($summary = $newFilteredFeed->get_summary()) {
          // Insert Authors and Categories into it
          $summary .= '        <br />' . PHP_EOL;
          if ($newFilteredFeed->get_authors()) {
            $summary .= '        <br />Author(s) : ' . get_array_as_string($newEntry->get_entry_authors()) . PHP_EOL;
          }
          if ($newFilteredFeed->get_categories()) {
            $summary .= '        <br />Categories: ' . get_array_as_string($newEntry->get_entry_categories()) . PHP_EOL;
          }
        }
        $newEntry->set_entry_summary($summary);

        // Set Content
        if ($content = $newFilteredFeed->get_content()) {
          // Modify content here as needed
        }
        $newEntry->set_entry_content($content);

        // Display debug view
        if ($myFeedDebug === true) {
          $newEntry->debug_entry();
          $newFilteredFeed->debug_filter();

          unset($newFilteredFeed);
          unset($newEntry);
          unset($newFeed);

          return;
        } else {
          $newEntry->create_entry();
        }
        // Add the entry identifier to the identifier_list
        array_push($identifier_list, $newEntry->get_entry_identifier());
      }
      unset($newEntry);
    }
    $newFeed->close_feed();
  }
  unset($newFilteredFeed);
  unset($newFeed);
} else {
  echo 'Please provide a "feed" parameter.';
}
