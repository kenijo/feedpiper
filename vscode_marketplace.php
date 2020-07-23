<?php

// Parameters
// ?category=xxx&sortBy=xxx&pageSize=xxx&pageNumber=xxx&sortOrder=xxx

// This code is specific to VS Code Extensions in order to retrieve a JSON file listing extensions based on feedfilter.php
$myFeedConfig['title'] = 'VS Code Extensions';

// Include configuration file
require_once 'include/inc.lib.php';

$url = "https://marketplace.visualstudio.com/_apis/public/gallery/extensionquery";

// Select a specific category
// Defaults to all extensions
$categoryValue = '';
if (isset($_GET['category'])) {
  $categorySelection = $_GET['category'];

  $categoryArray = array(
    "Azure",
    "Debuggers",
    "Extension Packs",
    "Formatters",
    "Keymaps",
    "Language Packs",
    "Linters",
    "Other",
    "Programming Languages",
    "SCM Providers",
    "Snippets",
    "Themes"
  );

  if ($key = array_search(strtolower($categorySelection), array_map('strtolower', $categoryArray))) {
    $categoryValue = '
    ,
    {
      "filterType": 5,
      "value": "' . $categoryArray[$key] . '"
    }
    ';
  }
}

// Sort extensions by
// Defaults to sorting by PublishedDate
$sortByValue = 10;
if (isset($_GET['sortBy'])) {
  $sortBySelection = $_GET['sortBy'];

  $sortByArray = array(
    1 => "UpdatedDate",
    2 => "Name",
    3 => "Publisher",
    4 => "Downloads",
    8 => "Trending",
    10 => "PublishedDate",
    12 => "Rating"
  );

  if ($key = array_search(strtolower($sortBySelection), array_map('strtolower', $sortByArray))) {
    $sortByValue = $sortByArray[$key];
  }
}

// Number of documents to retrieve
if (isset($_GET['pageSize'])) {
  $pageSize = $_GET['pageSize'];
} else {
  $pageSize = 50;
}

// Page number to retrieve
//  If pageSize=1 and pageNumber=2 then the result is extension 2.
//  It is equivalent of retrieving the second extension of pagesize=2 and pageNumber=1
$pageNumber = 1;
if (isset($_GET['pageNumber'])) {
  $pageNumber = $_GET['pageNumber'];
} else {
  $pageNumber = 1;
}

// Sort extensions Descending (0) or Ascending (1)
if (isset($_GET['sortOrder'])) {
  $sortOrder = $_GET['sortOrder'];
} else {
  $sortOrder = 0;
}

$json_data = '
{
  "filters": [
  {
    "criteria": [
    {
      "filterType": 8,
      "value": "Microsoft.VisualStudio.Code"
        },
        {
          "filterType": 10,
          "value": "target:\"Microsoft.VisualStudio.Code\""
        },
        {
          "filterType": 12,
          "value": "37888"
        }' . $categoryValue . '
      ],
      "pageSize": ' . $pageSize . ',
      "pageNumber": ' . $pageNumber . ',
      "sortBy": ' . $sortByValue . ',
      "sortOrder": ' . $sortOrder . ',
    }
  ],
  "flags": 870
}
';

$rest_api_post_header = array(
  'Accept: application/json; charset=utf-8; api-version=5.0-preview.1',
  'Content-Length: ' . strlen($json_data),
  'Cache-Control: no-cache',
  'Content-Type: application/json; charset=utf-8',
  'Pragma: no-cache',
  'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
);

if ((isset($_GET['debug'])) && ($_GET['debug'] === 'true')) {
  $myFeedDebug = true;
} else {
  $myFeedDebug = false;
}

if (!isset($myFeedConfig['title'])) {
  echo 'A "title" could not be found in the configuration file for: ' . $myFeed;
  return;
}

// Set the URL of the feed(s) you want to parse
if (isset($url)) {
  $result_json = CallRestAPI("POST", $url, $rest_api_post_header, $json_data);
  $json = json_decode($result_json, true);
} else {
  echo 'A "url" could not be found in the configuration file for: ' . $myFeed;
  return;
}

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

// Use GMT as the default time zone.
$date = new DateTime('now', new DateTimezone('GMT'));

$newFeed = new Feed($cfg['feed_format']);

$newFeed->set_feed_generator_name(SIMPLEPIE_NAME);
$newFeed->set_feed_generator_uri($_SERVER['REQUEST_URI']);
$newFeed->set_feed_generator_version(SIMPLEPIE_VERSION);
$newFeed->set_feed_icon(url_dir_path() . '/favicon.ico');
$newFeed->set_feed_id(url_file_path());
$newFeed->set_feed_link(url_file_path());
$newFeed->set_feed_link_alternate($url);
$newFeed->set_feed_logo(url_dir_path() . '/favicon.png');
$newFeed->set_feed_title($myFeedConfig['title']);
if ($cfg['feed_format'] == 'ATOM') {
  $date_format = DATE_ATOM;
} elseif ($cfg['feed_format'] == 'RSS') {
  $date_format = DATE_RSS;
}
$newFeed->set_feed_updated($date->format($date_format));

$parsed_url = parse_url($url);
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
$number_of_entry = 0;
foreach ($json['results'][0]['extensions'] as $entry) {
  if ($number_of_entry <= $pageSize) {
    $newEntry = new Entry($cfg['feed_format']);

    // Set Id
    $newEntry->set_entry_id($entry['publisher']['publisherId']);

    // Set Link
    $entry_link = 'https://marketplace.visualstudio.com/items?itemName=' . $entry['publisher']['publisherName'] . '.' . $entry['extensionName'];
    $newEntry->set_entry_link($entry_link);
    $newEntry->set_entry_link_original($url);

    // Set Identifier
    $newEntry->set_entry_identifier($entry_link);

    // Check if the identifier of the entry already exists
    // If it already exists then we skip it(remove duplicates)
    if (in_array($newEntry->get_entry_identifier(), $identifier_list) === false) {
      // Set Published Date
      $newEntry->set_entry_published($entry['publishedDate']);

      // Set Updated Date
      $newEntry->set_entry_updated($entry['lastUpdated']);

      // Set Title
      $newEntry->set_entry_title($entry['displayName']);

      // Set Authors
      $authors = explode(',', $entry['publisher']['displayName']);
      $newEntry->set_entry_authors($authors);

      // Set Categories
      $categories = array();
      if (is_array($entry['categories'])) {
        $categories = array_merge($categories, $entry['categories']);
      }
      if (is_array($entry['tags'])) {
        $categories = array_merge($categories, $entry['tags']);
      }
      $newEntry->set_entry_categories($categories);

      // Set Summary
      $newEntry->set_entry_summary($entry['shortDescription']);

      if ($entry['versions'][0]['files']) {
        foreach ($entry['versions'][0]['files'] as $key => $value) {
          if ($value["assetType"] == "Microsoft.VisualStudio.Services.Content.Changelog") {
            // $changelog = $value["source"];
          } elseif ($value["assetType"] == "Microsoft.VisualStudio.Services.Content.Details") {
            $details = $value["source"];
          } elseif ($value["assetType"] == "Microsoft.VisualStudio.Services.Icons.Default") {
            // $icon_default = $value["source"];
          } elseif ($value["assetType"] == "Microsoft.VisualStudio.Services.Icons.Small") {
            $icon_small = $value["source"];
          } elseif ($value["assetType"] == "Microsoft.VisualStudio.Services.VSIXPackage") {
            $VSIXPackage = $value["source"];
          }
        }
      }

      if ($entry['statistics']) {
        foreach ($entry['statistics'] as $key => $value) {
          if ($value["statisticName"] == "install") {
            $install = $value["value"];
          } elseif ($value["statisticName"] == "averagerating") {
            $averagerating = $value["value"];
          } elseif ($value["statisticName"] == "ratingcount") {
            $ratingcount = $value["value"];
          } elseif ($value["statisticName"] == "trendingdaily") {
            // $trendingdaily = $value["value"];
          } elseif ($value["statisticName"] == "trendingmonthly") {
            // $trendingmonthly = $value["value"];
          } elseif ($value["statisticName"] == "trendingweekly") {
            // $trendingweekly = $value["value"];
          } elseif ($value["statisticName"] == "updateCount") {
            // $updateCount = $value["value"];
          }
        }
      }

      // Set Content
      if ($icon_small != null) {
        $content = '<img alt="" src="' . $icon_small . '" style="width: 72px;">' . PHP_EOL;
      }
      $content .= '        <p>' . PHP_EOL;
      $content .= '          ' . $entry['shortDescription'] . PHP_EOL;
      $content .= '          <br />' . PHP_EOL;
      $content .= '          <span title="More from publisher"><a href="https://marketplace.visualstudio.com/publishers/' . $entry['publisher']['publisherName'] . '">' . $entry['publisher']['displayName'] . '</a></span>' . PHP_EOL;
      if (round($averagerating) == 0) {
        $rating = '&#9734;&#9734;&#9734;&#9734;&#9734;';
      } else if (round($averagerating) == 1) {
        $rating = '&#9733;&#9734;&#9734;&#9734;&#9734;';
      } else if (round($averagerating) == 2) {
        $rating = '&#9733;&#9733;&#9734;&#9734;&#9734;';
      } else if (round($averagerating) == 3) {
        $rating = '&#9733;&#9733;&#9733;&#9734;&#9734;';
      } else if (round($averagerating) == 4) {
        $rating = '&#9733;&#9733;&#9733;&#9733;&#9734;';
      } else {
        $rating = '&#9733;&#9733;&#9733;&#9733;&#9733;';
        if ($averagerating != null) {
          $averagerating = 0;
        }
        if ($ratingcount != null) {
          $ratingcount = 0;
        }
      }
      $content .= '          | <span title="Average Rating: ' . $averagerating . ' (Number of ratings: ' . $ratingcount . ')">' . $rating . '</span>' . PHP_EOL;
      $content .= '          | <span title="Install extension"><a href="vscode:' . $entry['publisher']['publisherName'] . '.' . $entry['extensionName'] . '">Install</a></span>' . PHP_EOL;
      $content .= '          | <span title="Download extension"><a href="' . $VSIXPackage . '">Download</a></span>' . PHP_EOL;
      $content .= '          <br />' . PHP_EOL;
      if (is_array($entry['categories'])) {
        $content .= '          <br />Categories: ';
        $delimiter = '';
        foreach ($entry['categories'] as $categorie) {
          $content .= $delimiter . $categorie;
          $delimiter = ', ';
        }
        $content .= PHP_EOL;
      }
      if (is_array($entry['tags'])) {
        $content .= '          <br />Tags: ';
        $delimiter = '';
        foreach ($entry['tags'] as $tag) {
          $content .= $delimiter . $tag;
          $delimiter = ', ';
        }
        $content .= PHP_EOL;
      }
      $content .= '        </p>' . PHP_EOL;

      // If there is no URL, we don't have details to retrieve
      // We also skip the details when sorting by PublishedDate
      // because for some reason, they are not always retrieved well
      if ($details != null && $sortByValue != 10) {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $rest_api_get_header = array(
          'Accept: text/markdown; charset=utf-8; api-version=5.0-preview.1',
          'Cache-Control: no-cache',
          'Content-Type: text/markdown; charset=utf-8',
          'Pragma: no-cache',
          'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
        );
        $markdown = CallRestAPI("GET", $details, $rest_api_get_header);
        $content .= $parsedown->text($markdown);
      }
      $newEntry->set_entry_content($content);

      // Display debug view
      if ($myFeedDebug === true) {
        $newEntry->debug_entry();

        unset($newEntry);
        unset($newFeed);

        return;
      } else {
        $newEntry->create_entry();
      }

      // Add the entry identifier to the identifier_list
      array_push($identifier_list, $newEntry->get_entry_identifier());
    }
    $number_of_entry++;

    unset($authors);
    unset($averagerating);
    unset($categorie);
    unset($categories);
    unset($content);
    unset($details);
    unset($entry_link);
    unset($icon_small);
    unset($install);
    unset($key);
    unset($markdown);
    unset($newEntry);
    unset($parsedown);
    unset($rating);
    unset($ratingcount);
    unset($tag);
    unset($url);
    unset($value);
    unset($VSIXPackage);
  }
}
$newFeed->close_feed();
unset($newFeed);
