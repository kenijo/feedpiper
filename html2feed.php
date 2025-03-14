﻿<?php

// Include libraries
require_once __DIR__ . '/library/includes.php';

if (isset($_GET['page'])) {
  $myPage = $_GET['page'];

  if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    $myPageDebug = true;
  } else {
    $myPageDebug = false;
  }

  if (isset($html2feed[$myPage])) {
    $myPageConfig = $html2feed[$myPage];
  } else {
    echo 'A configuration could not be found in the configuration file for: ' . $myPage;
    return;
  }

  if (!isset($myPageConfig['page_title'])) {
    echo 'A "title" could not be found in the configuration file for: ' . $myPage;
    return;
  }

  if (!isset($myPageConfig['page_url'])) {
    echo 'A "url" could not be found in the configuration file for: ' . $myPage;
    return;
  }

  // Send the content-type header with correct encoding
  if ($myPageDebug === true) {
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
  $date = new \DateTime('now', new \DateTimezone('GMT'));

  $newFeed = new \Feed($cfg['feed_format']);

  $newFeed->setGeneratorName('Simple HTML DOM');
  $newFeed->setGeneratorUri($_SERVER['REQUEST_URI']);
  $newFeed->setIcon(urlDirPath() . '/favicon.ico');
  $newFeed->setId(urlFilePath());
  $newFeed->setLink(urlFilePath());
  $newFeed->setLinkAlternate($myPageConfig['page_url']);
  $newFeed->setLogo(urlDirPath() . '/favicon.png');
  $newFeed->setTitle($myPageConfig['page_title']);
  if ($cfg['feed_format'] == 'ATOM') {
    $date_format = DATE_ATOM;
  } elseif ($cfg['feed_format'] == 'RSS') {
    $date_format = DATE_RSS;
  }
  $newFeed->setUpdated($date->format($date_format));

  $parsed_url = parse_url($myPageConfig['page_url']);
  $website_link = $parsed_url['scheme'] . '://' . $parsed_url['host'];
  $newFeed->setWebsiteLink($website_link);

  // Display or Debug feed
  if ($myPageDebug === true) {
    $newFeed->debug();
  } else {
    $newFeed->open();
  }

  // PHP Simple HTML DOM
  //  http://simplehtmldom.sourceforge.net/

  // Set a default context browser
  $context = stream_context_create([
    'http' => [
      'header' => ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:112.0) Gecko/20100101 Firefox/112.0'],
    ],
  ]);

  // Load HTML from an URL, Create a DOM object
  $newDomHtml = file_get_html($myPageConfig['page_url'], false, $context);

  // Loop through html pulling feed items out
  if (isset($myPageConfig['entry'])) {
    foreach ($newDomHtml->find($myPageConfig['entry']) as $entry) {
      $date->modify('-1 second');

      $newEntry = new \Entry($cfg['feed_format']);

      $parsed_page_url = parse_url($myPageConfig['page_url']);

      // Set Published Date
      $newEntry->setPublished($date->format(DATE_ATOM));

      // Set Updated Date
      $newEntry->setUpdated($date->format(DATE_ATOM));

      if (isset($myPageConfig['link'])) {
        // Set Link
        $link = $myPageConfig['link'];
        $link = $entry->find($link, 0)->href;
        // If $link is not a full URL then we rebuild it from the feed URL
        $parsed_link = parse_url($link);
        if (!isset($parsed_link['scheme'])) {
          $link = $parsed_page_url['scheme'] . '://' . $parsed_page_url['host'] . $link;
        }

        $link ? $link : $link = null;
        $newEntry->setLink($link);

        // Set Id
        $newEntry->setId($link);

        // Set Identifier
        //$identifier = basename($link) . '@' . parse_url($link)['host'];
        //$identifier ? $identifier : $identifier = null;
        //$newEntry->set_entry_identifier($identifier);
        $newEntry->setIdentifier($link);
      } else {
        echo 'A "link" could not be found in the configuration file for: ' . $myPage;
        break;
      }

      if (isset($myPageConfig['title'])) {
        // Set Title
        $title = $myPageConfig['title'];
        $title = trim($entry->find($title, 0)->plaintext);

        if(empty($title)) {
          $title = $myPageConfig['thumbnail'];
          $title = trim($entry->find($title, 0)->alt);
        }

        $title = preg_replace('#' . '\s+' . '#', ' ', $title);
        $title = html_entity_decode($title, ENT_NOQUOTES, 'UTF-8');
        $title ? $title : $title = null;
        $newEntry->setTitle($title);
      } else {
        echo 'An "title" could not be found in the configuration file for: ' . $myPage;
        break;
      }

      if (isset($myPageConfig['thumbnail'])) {
        // Set Thumbnail
        $thumbnail = $myPageConfig['thumbnail'];
        $thumbnail = $entry->find($thumbnail, 0)->src;

        // If $thumbnail is not a full URL then we rebuild it from the feed URL
        $parsed_thumbnail = parse_url($thumbnail);
        if (!isset($parsed_thumbnail['scheme'])) {
          $thumbnail = $parsed_page_url['scheme'] . '://' . $parsed_page_url['host'] . $thumbnail;
        }
        $thumbnail ? $thumbnail : $thumbnail = null;

        // Set Enclosure
        $newEntry->setEnclosureLink($thumbnail);
      }

      if (isset($myPageConfig['author'])) {
        // Set Authors
        $authors = $myPageConfig['author'];
        $authors = trim($entry->find($authors, 0)->plaintext);
        $authors = html_entity_decode($authors);
        if (isset($authors)) {
          $authors = str_replace('&', '', $authors);
          $authors = preg_replace('#' . '\s+' . '#', ' ', $authors);
        }
      } else {
        $authors = $myPageConfig['page_title'];
      }
      $newEntry->setAuthors([$authors]);

      if (isset($myPageConfig['category'])) {
        // Set Categories
        $categories = $myPageConfig['category'];
        $categories = trim($entry->find($categories, 0)->plaintext);
        $categories = html_entity_decode($categories);
        if (isset($categories)) {
          $categories = str_replace('&', '', $categories);
          $categories = removeAccents($categories);
          $categories = explode(' ', $categories);
          $categories = array_map('strtolower', $categories);
          $categories = array_unique($categories);
          $categories = array_filter($categories);
          sort($categories);
        } else {
          $categories = null;
        }
      } else {
        $categories = null;
      }
      $newEntry->setCategories($categories);

      if (isset($myPageConfig['description'])) {
        // Set Summary
        $description = $myPageConfig['description'];
        $description = $entry->find($description, 0)->outertext;
        $summary = null;
        if (isset($thumbnail)) {
          $summary .= '<img src="' . $thumbnail . '" alt="' . $title . '" />';
        }
        $summary .= '<p>' . $description . '</p>' . PHP_EOL;

        // Insert Authors and Categories into it
        $summary .= '        <br />' . PHP_EOL;
        if ($authors) {
          $summary .= '        <br />Author(s) : ' . $authors . PHP_EOL;
        }
        if ($categories) {
          $summary .= '        <br />Categories: ' . convertArrayToCommaSeparatedString($categories) . PHP_EOL;
        }
        $newEntry->setSummary($summary);

        // Set Content
        /*
        $url = 'http://www.readability.com/m?url=' . $link;
        $ch = curl_init($url); // initialize curl with given url
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // set  useragent
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // write the response to a variable
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects if any
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // max. seconds to execute
        curl_setopt($ch, CURLOPT_FAILONERROR, 1); // stop when it encounters an error
        $ch ? $newEntry->set_entry_content(@curl_exec($ch)) : null;
        */
      }

      // Debug or display entry
      if ($myPageDebug === true) {
        $newEntry->debug();

        $newDomHtml->clear();
        unset($newDomHtml);
        unset($newEntry);
        unset($newFeed);

        return;
      } else {
        $newEntry->add();
      }
      unset($newEntry);
    }
  } else {
    echo 'An "entry" could not be found in the configuration file for: ' . $myPage;
  }
  $newFeed->close();

  $newDomHtml->clear();
  unset($newDomHtml);
  unset($newFeed);
} else {
  echo 'Please provide a "page" parameter.';
}
