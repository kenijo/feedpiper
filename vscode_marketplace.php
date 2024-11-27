<?php

// Parameters
// ?category=xxx&sortBy=xxx&pageSize=xxx&pageNumber=xxx&sortOrder=xxx

// This code is specific to VS Code Extensions in order to retrieve a JSON file listing extensions based on feedfilter.php
$feedConfig['title'] = 'VS Code Extensions';

// Include libraries
require_once __DIR__ . '/library/inc.lib.php';

$url = "https://marketplace.visualstudio.com/_apis/public/gallery/extensionquery";

// Select a specific category
// Defaults to all extensions
$categoryValue = '';
if (isset($_GET['category'])) {
  $categorySelection = $_GET['category'];

  $categoryArray = [
    "All categories",
    "Azure",
    "Data Science",
    "Debuggers",
    "Education",
    "Extension Packs",
    "Formatters",
    "Keymaps",
    "Language Packs",
    "Linters",
    "Machine Learning",
    "Notebooks",
    "Other",
    "Programming Languages",
    "SCM Providers",
    "Snippets",
    "Testing",
    "Themes",
    "Visualization"
  ];

  if ($nppKey = array_search(strtolower($categorySelection), array_map('strtolower', $categoryArray))) {
    $categoryValue = '
    ,
    {
      "filterType": 5,
      "value": "' . $categoryArray[$nppKey] . '"
    }
    ';
  }
}

// Sort extensions by
// Defaults to sorting by PublishedDate
$sortByValue = 5;
if (isset($_GET['sortBy'])) {
  $sortBySelection = $_GET['sortBy'];

  $sortByArray = [
    1 => "UpdatedDate",
    2 => "Name",
    3 => "Publisher",
    4 => "Installs",
    5 => "PublishedDate",
    6 => "Rating",
    7 => "Trending"
  ];

  if ($nppKey = array_search(strtolower($sortBySelection), array_map('strtolower', $sortByArray))) {
    $sortByValue = $sortByArray[$nppKey];
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
      "sortOrder": ' . $sortOrder . '
    }
  ],
  "flags": 870
}
';

$rest_api_post_header = [
  'Accept: application/json; charset=utf-8; api-version=7.1-preview.1',
  'Content-Length: ' . strlen($json_data),
  'Cache-Control: no-cache',
  'Content-Type: application/json; charset=utf-8',
  'Pragma: no-cache',
  'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:112.0) Gecko/20100101 Firefox/112.0'
];

if ((isset($_GET['debug'])) && ($_GET['debug'] === 'true')) {
  $debug = true;
} else {
  $debug = false;
}

if (!isset($feedConfig['title'])) {
  echo 'A "title" could not be found in the configuration file for: ' . $feed;
  return;
}

// Set the URL of the feed(s) you want to parse
if (isset($url)) {
  $result_json = callRestAPI("POST", $url, $rest_api_post_header, $json_data);
  $json = json_decode($result_json, true);
} else {
  echo 'A "url" could not be found in the configuration file for: ' . $feed;
  return;
}

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

// Use GMT as the default time zone.
$date = new DateTime('now', new DateTimezone('GMT'));

$newFeed = new Feed($cfg['feed_format']);

$newFeed->setGeneratorName(\SimplePie\SimplePie::NAME);
$newFeed->setGeneratorUri($_SERVER['REQUEST_URI']);
$newFeed->setGeneratorVersion(\SimplePie\SimplePie::VERSION);
$newFeed->setIcon(urlDirPath() . '/favicon.ico');
$newFeed->setId(urlFilePath());
$newFeed->setLink(urlFilePath());
$newFeed->setLinkAlternate($url);
$newFeed->setLogo(urlDirPath() . '/favicon.png');
$newFeed->setTitle($feedConfig['title']);
if ($cfg['feed_format'] == 'ATOM') {
  $date_format = DATE_ATOM;
} elseif ($cfg['feed_format'] == 'RSS') {
  $date_format = DATE_RSS;
}
$newFeed->setUpdated($date->format($date_format));

$parsed_url = parse_url($url);
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
$number_of_entry = 0;

foreach ($json['results'][0]['extensions'] as $entry) {
  if ($number_of_entry <= $pageSize) {
    $newEntry = new Entry($cfg['feed_format']);

    // Set Id
    $newEntry->setId($entry['publisher']['publisherId']);

    // Set Link
    $entry_link = 'https://marketplace.visualstudio.com/items?itemName=' . $entry['publisher']['publisherName'] . '.' . $entry['extensionName'];
    $newEntry->setLink($entry_link);

    // Set Identifier
    $newEntry->setIdentifier($entry_link);

    // Check if the identifier of the entry already exists
    // If it already exists then we skip it(remove duplicates)
    if (in_array($newEntry->getIdentifier(), $identifier_list) === false) {
      // Set Published Date
      $newEntry->setPublished($entry['publishedDate']);

      // Set Updated Date
      $newEntry->setUpdated($entry['lastUpdated']);

      // Set Title
      $newEntry->setTitle($entry['displayName']);

      // Set Authors
      $authors = explode(',', $entry['publisher']['displayName']);
      $newEntry->setAuthors($authors);

      // Set Categories
      $categories = [];
      if (is_array($entry['categories'])) {
        $categories = array_merge($categories, $entry['categories']);
      }
      if (is_array($entry['tags'])) {
        $categories = array_merge($categories, $entry['tags']);
      }
      $newEntry->setCategories($categories);

      // Set Summary
      $newEntry->setSummary($entry['shortDescription']);

      if ($entry['versions'][0]['files']) {
        foreach ($entry['versions'][0]['files'] as $nppKey => $vscodeKey) {
          if ($vscodeKey["assetType"] == "Microsoft.VisualStudio.Services.Content.Changelog") {
            // $changelog = $value["source"];
          } elseif ($vscodeKey["assetType"] == "Microsoft.VisualStudio.Services.Content.Details") {
            $details = $vscodeKey["source"];
          } elseif ($vscodeKey["assetType"] == "Microsoft.VisualStudio.Services.Icons.Default") {
            // $icon_default = $value["source"];
          } elseif ($vscodeKey["assetType"] == "Microsoft.VisualStudio.Services.Icons.Small") {
            $icon_small = $vscodeKey["source"];
          } elseif ($vscodeKey["assetType"] == "Microsoft.VisualStudio.Services.VSIXPackage") {
            $VSIXPackage = $vscodeKey["source"];
          }
        }
      }

      if ($entry['statistics']) {
        foreach ($entry['statistics'] as $nppKey => $vscodeKey) {
          if ($vscodeKey["statisticName"] == "install") {
            $install = $vscodeKey["value"];
          } elseif ($vscodeKey["statisticName"] == "averagerating") {
            $averagerating = $vscodeKey["value"];
          } elseif ($vscodeKey["statisticName"] == "ratingcount") {
            $ratingcount = $vscodeKey["value"];
          } elseif ($vscodeKey["statisticName"] == "trendingdaily") {
            // $trendingdaily = $value["value"];
          } elseif ($vscodeKey["statisticName"] == "trendingmonthly") {
            // $trendingmonthly = $value["value"];
          } elseif ($vscodeKey["statisticName"] == "trendingweekly") {
            // $trendingweekly = $value["value"];
          } elseif ($vscodeKey["statisticName"] == "updateCount") {
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
        foreach ($entry['categories'] as $category) {
          $content .= $delimiter . $category;
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
        $rest_api_get_header = [
          'Accept: text/markdown; charset=utf-8; api-version=5.0-preview.1',
          'Cache-Control: no-cache',
          'Content-Type: text/markdown; charset=utf-8',
          'Pragma: no-cache',
          'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:112.0) Gecko/20100101 Firefox/112.0'
        ];
        $markdown = callRestAPI("GET", $details, $rest_api_get_header);
        $content .= $parsedown->text($markdown);
      }
      $newEntry->setContent($content);

      // Display debug view
      if ($debug === true) {
        $newEntry->debug();

        unset($newEntry);
        unset($newFeed);

        return;
      } else {
        $newEntry->add();
      }

      // Add the entry identifier to the identifier_list
      array_push($identifier_list, $newEntry->getIdentifier());
    }
    $number_of_entry++;

    unset($authors);
    unset($averagerating);
    unset($category);
    unset($categories);
    unset($content);
    unset($details);
    unset($entry_link);
    unset($icon_small);
    unset($install);
    unset($nppKey);
    unset($markdown);
    unset($newEntry);
    unset($parsedown);
    unset($rating);
    unset($ratingcount);
    unset($tag);
    unset($url);
    unset($vscodeKey);
    unset($VSIXPackage);
  }
}
$newFeed->close();
unset($newFeed);
