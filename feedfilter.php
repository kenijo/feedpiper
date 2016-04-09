<?php
// TODO: find the first feed in the conf and use it as the alt link
// Include configuration file
require_once 'include/inc.lib.php';
require_once 'config/feedfilter.conf.php';

if (isset($_GET['feed']))
{
  $myFeed = $_GET['feed'];

  if (isset($_GET['debug']) && $_GET['debug'] === 'true')
  {
    $myFeedDebug = true;
  }
  else
  {
    $myFeedDebug = false;
  }

  if (isset($cfg[$myFeed]))
  {
    $myFeedConfig = $cfg[$myFeed];
  }
  else
  {
    echo 'A configuration could not be found in the configuration file for: ' . $myFeed;
    return;
  }

  // Create a new instance of SimplePie
  $newSimplePie = new SimplePie();

  // Set the length of time(in seconds) that the contents of a feed will be cached
  if (isset($cfg['cache_length']))
  {
    $newSimplePie->set_cache_duration($cfg['cache_length']);
  }

  // Set MySQL caching
  // Otherwise we will use file caching (cache folder must be system writable)
  if (isset($cfg['mysql']))
  {
    $location = 'mysql://' . $cfg['mysql']['user'] . ':' . $cfg['mysql']['password'] . '@' . $cfg['mysql']['host'] .':' . $cfg['mysql']['port'] . '/' . $cfg['mysql']['database'];
  }
  else
  {
    $location = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['REQUEST_URI']) . '/cache';
  }
  $newSimplePie->set_cache_location($location);

  // Force SimplePie to use fsockopen() instead of cURL
  if (isset($cfg['curl']) && $cfg['curl'] === false)
  {
    $newSimplePie->force_fsockopen(true);
  }

  if (!isset($myFeedConfig['title']))
  {
    echo 'A "title" could not be found in the configuration file for: ' . $myFeed;
    return;
  }

  // Set the URL of the feed(s) you want to parse
  if (isset($myFeedConfig['url']))
  {
    $newSimplePie->set_feed_url($myFeedConfig['url']);
  }
  else
  {
    echo 'A "url" could not be found in the configuration file for: ' . $myFeed;
    return;
  }

  // Send the content-type header with correct encoding
  if ($myFeedDebug === true)
  {
    $content_type = 'text/plain';
  }
  elseif (isset($cfg['feed_format']) && $cfg['feed_format'] == 'ATOM')
  {
    $content_type = 'application/atom+xml';
  }
  elseif (isset($cfg['feed_format']) && $cfg['feed_format'] == 'RSS')
  {
    $content_type = 'application/rss+xml';
  }
  else
  {
    $content_type = 'application/xml';
  }
  $newSimplePie->handle_content_type($content_type);
  header('Content-type: ' . $content_type . '; charset=utf-8');

  // SimplePie API Documentation
  // http://simplepie.org/api/class-SimplePie_Item.html
  // Initialize the SimplePie feed object
  if ($newSimplePie->init())
  {
    // Use GMT as the default time zone.
    $date = new DateTime('now', new DateTimezone('GMT'));

    $newFilteredFeed = new FilteredFeed($cfg['feed_format']);
    $newFeed         = new Feed($cfg['feed_format']);

    // Cleanup the filters
    if (isset($myFeedConfig['filter']))
    {
      $newFilteredFeed->set_filter(cleanArray($myFeedConfig['filter'], 'strtolower'));
    }

    // Cleanup the global filters
    if (isset($cfg['global_filter']))
    {
      $newFilteredFeed->set_global_filter(cleanArray($cfg['global_filter'], 'strtolower'));
    }

    $newFeed->set_feed_generator_name(SIMPLEPIE_NAME);
    $newFeed->set_feed_generator_uri($_SERVER['REQUEST_URI']);
    $newFeed->set_feed_generator_version(SIMPLEPIE_VERSION);
    $newFeed->set_feed_icon(url_dir_path() . '/favicon.ico');
    $newFeed->set_feed_id(url_file_path());
    $newFeed->set_feed_link(url_file_path());
    $newFeed->set_feed_link_alternate($myFeedConfig['url'][0]);
    $newFeed->set_feed_logo(url_dir_path() . '/favicon.png');
    $newFeed->set_feed_title($myFeedConfig['title']);
    if ($cfg['feed_format'] == 'ATOM')
    {
      $date_format = DATE_ATOM;
    }
    elseif ($cfg['feed_format'] == 'RSS')
    {
      $date_format = DATE_RSS;
    }
    $newFeed->set_feed_updated($date->format($date_format));

    $newFeed->set_feed_website_link();

    // Display or Debug feed
    if ($myFeedDebug === true)
    {
      $newFeed->debug_feed();
    }
    else
    {
      $newFeed->open_feed();
    }

    // Create an array of unique identifiers to skip duplicate entries
    $identifier_list = Array();

    foreach ($newSimplePie->get_items() as $entry)
    {
      $newEntry = new Entry($cfg['feed_format']);

      // Select a specific entry for debug purpose
      if ($myFeedDebug === true)
      {
        if (isset($_GET['entry']))
        {
          $e = $_GET['entry'];
        }
        else
        {
          $e = 0;
        }
        $array = $newSimplePie->get_items();
        $newFilteredFeed->filter($array[$e]);
      }
      else
      {
        $newFilteredFeed->filter($entry);
      }

      // Set Id
      $newEntry->set_entry_id($newFilteredFeed->get_id());

      // Set Link
      $newEntry->set_entry_link($newFilteredFeed->get_link());

      // Set Identifier
      //$identifier = basename($newFilteredFeed->get_id()) . '@' . parse_url($newFilteredFeed->get_link())['host'];
      //$identifier ? $identifier : $identifier = null;
      //$newEntry->set_entry_identifier($identifier);
      $newEntry->set_entry_identifier($newFilteredFeed->get_link());

      // Check if the identifier of the entry already exists
      // If it already exists then we skip it(remove duplicates)
      if ((in_array($newEntry->get_entry_identifier(), $identifier_list) === false && $newFilteredFeed->get_skip() !== true) || $myFeedDebug === true)
      {
        // Set Published Date
        $newEntry->set_entry_published($newFilteredFeed->get_date());

        // Set Updated Date
        $newEntry->set_entry_updated($newFilteredFeed->get_date());

        // Set Title
        $title = $newFilteredFeed->get_title();
        // START - SPECIAL CODE
        // If feed is 'dropbox' then use part of get_summary() as the title
        if ($myFeed == 'dropbox' && $newFilteredFeed->get_summary())
        {
          $title = 'Deleted: ' . between('>', '</a>.<br />', $newFilteredFeed->get_summary());
          $newFilteredFeed->set_title($title);
        }
        // END   - SPECIAL CODE
        $newEntry->set_entry_title($title);

        // Set Enclosure
        $newEntry->set_entry_enclosure_length($newFilteredFeed->get_enclosure_length());
        $newEntry->set_entry_enclosure_link($newFilteredFeed->get_enclosure_link());
        $newEntry->set_entry_enclosure_type($newFilteredFeed->get_enclosure_type());

        // Set Auhtors
        $newEntry->set_entry_authors($newFilteredFeed->get_authors());

        // Set Categories
        $newEntry->set_entry_categories($newFilteredFeed->get_categories());

        // Set Summary
        if ($summary = $newFilteredFeed->get_summary())
        {
          // START  - SPECIAL CODE
          // If feed is 'king' then remove unusable image links
          if ($myFeed == 'king')
          {
            if (preg_match('#'.'<a.*?<img.*?video.*?></a>'.'#imu', $summary) !== 0)
            {
              $summary = preg_replace('#'.'<a.*?<img.*?video.*?></a>'.'#imu', '', $summary);
            }
            if (preg_match('#'.'<a.*?<img.*?src="http://www.king5.com/media/photos".*?></a>'.'#imu', $summary) !== 0)
            {
              $summary = preg_replace('#'.'<a.*?<img.*?src="http://www.king5.com/media/photos".*?></a>'.'#imu', '', $summary);
            }
            $summary = preg_replace('#'.'<div.*?><a title="Like on Facebook".*'.'#imu', '', $summary);
          }
          // END   - SPECIAL CODE

          // Insert Authors and Categories into it
          $summary .= '        <br />' . PHP_EOL;
          if ($newFilteredFeed->get_authors())
          {
            $summary .= '        <br />Author(s) : ' . get_array_as_string($newEntry->get_entry_authors()) . PHP_EOL;
          }
          if ($newFilteredFeed->get_categories())
          {
            $summary .= '        <br />Categories: ' . get_array_as_string($newEntry->get_entry_categories()) . PHP_EOL;
          }
        }
        $newEntry->set_entry_summary($summary);

        // Set Content
        if ($content = $newFilteredFeed->get_content())
        {
          // START  - SPECIAL CODE
          // If feed is 'king' then remove unusable image links
          if ($myFeed == 'king')
          {
            if (preg_match('#'.'<a.*?<img.*?video.*?></a>'.'#imu', $content) !== 0)
            {
              $content = preg_replace('#'.'<a.*?<img.*?video.*?></a>'.'#imu', '', $content);
            }
            if (preg_match('#'.'<a.*?<img.*?src="http://www.king5.com/media/photos".*?></a>'.'#imu', $content) !== 0)
            {
              $content = preg_replace('#'.'<a.*?<img.*?src="http://www.king5.com/media/photos".*?></a>'.'#imu', '', $content);
            }
            $content = preg_replace('#'.'<div.*?><a title="Like on Facebook".*'.'#imu', '', $content);
          }
          // END   - SPECIAL CODE
        }
        $newEntry->set_entry_content($content);

        // Display debug view
        if ($myFeedDebug === true)
        {
          $newEntry->debug_entry();
          $newFilteredFeed->debug_filter();

          unset($newFilteredFeed);
          unset($newEntry);
          unset($newFeed);
          unset($newSimplePie);

          return;
        }
        else
        {
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
  unset($newSimplePie);
}
else
{
  echo 'Please provide a "feed" parameter.';
}
