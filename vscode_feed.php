<?php

// This code is specific to VS Code Extensions based on feedfilter.php
$myFeedConfig['title'] = 'VS Code Extensions';
$myFeedConfig['url']   = 'https://vscode.blob.core.windows.net/gallery/index';

// Include configuration file
require_once 'include/inc.lib.php';

if ((isset($_GET['debug'])) && ($_GET['debug'] === 'true')) {
  $myFeedDebug = true;
} else {
  $myFeedDebug = false;
}

if (!isset($myFeedConfig['title']))
{
  echo 'A "title" could not be found in the configuration file for: ' . $myFeed;
  return;
}

// Set the URL of the feed(s) you want to parse
if (isset($myFeedConfig['url']))
{
  $json_data = get_remote_data($myFeedConfig['url']);
  $json = json_decode($json_data, true);

  usort($json['results'][0]['extensions'], function($a, $b) {
    return (strtotime($a['publishedDate']) > strtotime($b['publishedDate']) ? -1 : 1);
  });
}
else
{
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
$newFeed->set_feed_link_alternate($myFeedConfig['url']);
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

$parsed_url = parse_url($myFeedConfig['url']);
$website_link = $parsed_url['scheme'] . '://' . $parsed_url['host'];
$newFeed->set_feed_website_link($website_link);

// Display or Debug feed
if ($myFeedDebug === true) {
  $newFeed->debug_feed();
} else {
  $newFeed->open_feed();
}

// Create an array of unique identifiers to skip duplicate entries
$identifier_list = Array();
$number_of_entry = 0;
foreach ($json['results'][0]['extensions'] as $entry)
{
  if($number_of_entry <= 20) {
    $newEntry = new Entry($cfg['feed_format']);

    // Set Id
    $newEntry->set_entry_id($entry['publisher']['publisherId']);

    // Set Link
    $entry_link = 'https://marketplace.visualstudio.com/items?itemName=' . $entry['publisher']['publisherName'] . '.' . $entry['extensionName'];
    $newEntry->set_entry_link($entry_link);
    $newEntry->set_entry_link_original($myFeedConfig['url']);

    // Set Identifier
    $newEntry->set_entry_identifier($entry_link);

    // Check if the identifier of the entry already exists
    // If it already exists then we skip it(remove duplicates)
    if (in_array($newEntry->get_entry_identifier(), $identifier_list) === false)
    {
      // Set Published Date
      $newEntry->set_entry_published($entry['publishedDate']);

      // Set Updated Date
      $newEntry->set_entry_updated($entry['lastUpdated']);

      // Set Title
      $newEntry->set_entry_title($entry['displayName']);

      // Set Auhtors
      $authors = explode(',', $entry['publisher']['displayName']);
      $newEntry->set_entry_authors($authors);

      // Set Categories
      $categories = array();
      if(is_array($entry['categories'])) {
        $categories = array_merge($categories, $entry['categories']);
      }
      if(is_array($entry['tags'])) {
        $categories = array_merge($categories, $entry['tags']);
      }
      $newEntry->set_entry_categories($categories);

      // Set Summary
      $newEntry->set_entry_summary($entry['shortDescription']);

      // Set Content
      $content = '<img alt="" src="' . $entry['versions'][0]['fallbackAssetUri'] . '/Microsoft.VisualStudio.Services.Icons.Default" style="max-width: 128px; position: relative;"><br />' . PHP_EOL;
      $content .= '        <h2>' . $entry['displayName'] . '</h2>' . PHP_EOL;
      $content .= '        <hr>' . PHP_EOL;
      $content .= '        ' . $entry['shortDescription'] . '<br />' . PHP_EOL;
      $content .= '        <br />' . PHP_EOL;
      $content .= '        <table><tbody>' . PHP_EOL;
      $content .= '        <tr><td><div>Categories</div></td><td><div>' . $entry['categories'][0] . '</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>Tags</div></td><td><div>' . $entry['tags'][0] . '</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>&nbsp;</div></td><td><div>&nbsp;</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>Version</div></td><td><div>' . $entry['versions'][0]['version'] . '</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>Published</div></td><td><div>' . date('m/d/Y h:i:s A', strtotime($entry['publishedDate'])) . '</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>Last Update</div></td><td><div>' . date('m/d/Y h:i:s A', strtotime($entry['lastUpdated'])) . '</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>&nbsp;</div></td><td><div>&nbsp;</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>Number of Installs</div></td><td><div>' . $entry['statistics'][0]['value'] . '</div></td></tr>' . PHP_EOL;
      if(round($entry['statistics'][1]['value']) == 0) {
        $rating = '&#9734;&#9734;&#9734;&#9734;&#9734;';
      } else if(round($entry['statistics'][1]['value']) == 1) {
        $rating = '&#9733;&#9734;&#9734;&#9734;&#9734;';
      } else if(round($entry['statistics'][1]['value']) == 2) {
        $rating = '&#9733;&#9733;&#9734;&#9734;&#9734;';
      } else if(round($entry['statistics'][1]['value']) == 3) {
        $rating = '&#9733;&#9733;&#9733;&#9734;&#9734;';
      } else if(round($entry['statistics'][1]['value']) == 4) {
        $rating = '&#9733;&#9733;&#9733;&#9733;&#9734;';
      } else {
        $rating = '&#9733;&#9733;&#9733;&#9733;&#9733;';
      }
      $content .= '        <tr><td><div>Average Rating</div></td><td><div>' . $rating . '</div></td></tr>' . PHP_EOL;
      $content .= '        <tr><td><div>&nbsp;</div></td><td><div>&nbsp;</div></td></tr>' . PHP_EOL;
      if(is_array($categories)) {
        $content .= '        <tr><td><div>Categories</div></td><td><div>';
        $delimiter = '';
        foreach($categories as $categorie) {
          $content .= $delimiter . $categorie;
          $delimiter = ', ';
        }
        $content .= '</div></td></tr>' . PHP_EOL;
      }
      $content .= '        </tbody></table>' . PHP_EOL;

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
  }
  unset($newEntry);
}
$newFeed->close_feed();
unset($newFeed);


//See Updates and explanation at: https://github.com/tazotodua/useful-php-scripts/
function get_remote_data($url, $post_paramtrs = false, $return_full_array = false) {
  $c = curl_init();
  curl_setopt($c, CURLOPT_URL, $url);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  //if parameters were passed to this function, then transform into POST method.. (if you need GET request, then simply change the passed URL)
  if ($post_paramtrs) {
    curl_setopt($c, CURLOPT_POST, TRUE);
    curl_setopt($c, CURLOPT_POSTFIELDS, "var1=bla&".$post_paramtrs);
  }
  curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
  curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
  //We'd better to use the above command, because the following command gave some weird STATUS results..
  //$header[0]= $user_agent="User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0"; $header[]="Cookie:CookieName1=Value;"; $header[]="Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; $header[]="Cache-Control: max-age=0"; $header[]="Connection: keep-alive"; $header[]="Keep-Alive: 300"; $header[]="Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; $header[] = "Accept-Language: en-us,en;q=0.5"; $header[] = "Pragma: "; curl_setopt($c, CURLOPT_HEADER, true); curl_setopt($c, CURLOPT_HTTPHEADER, $header);

  curl_setopt($c, CURLOPT_MAXREDIRS, 10);
  //if SAFE_MODE or OPEN_BASEDIR is set,then FollowLocation cant be used.. so...
  $follow_allowed = (ini_get('open_basedir') || ini_get('safe_mode')) ? false : true;
  if ($follow_allowed) {
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
  }
  curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
  curl_setopt($c, CURLOPT_REFERER, $url);
  curl_setopt($c, CURLOPT_TIMEOUT, 60);
  curl_setopt($c, CURLOPT_AUTOREFERER, true);
  curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
  $data = curl_exec($c);
  $status = curl_getinfo($c);
  curl_close($c);

  preg_match('/(http(|s)):\/\/(.*?)\/(.*\/|)/si', $status['url'], $link);
  //correct assets URLs(i.e. retrieved url is: http://site.com/DIR/SUBDIR/page.html... then href="./image.JPG" becomes href="http://site.com/DIR/SUBDIR/image.JPG", but href="/image.JPG" needs to become href="http://site.com/image.JPG")

  //inside all links(except starting with HTTP,javascript:,HTTPS,//,/ ) insert that current DIRECTORY url (href="./image.JPG" becomes href="http://site.com/DIR/SUBDIR/image.JPG")
  $data = preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/|\/)).*?)(\'|\")/si', '$1=$2'.$link[0].
    '$3$4$5', $data);
  //inside all links(except starting with HTTP,javascript:,HTTPS,//) insert that DOMAIN url (href="/image.JPG" becomes href="http://site.com/image.JPG")
  $data = preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/)).*?)(\'|\")/si', '$1=$2'.$link[1].
    '://'.$link[3].
    '$3$4$5', $data);
  // if redirected, then get that redirected page
  if ($status['http_code'] == 301 || $status['http_code'] == 302) {
    //if we FOLLOWLOCATION was not allowed, then re-get REDIRECTED URL
    //p.s. WE dont need "else", because if FOLLOWLOCATION was allowed, then we wouldnt have come to this place, because 301 could already auto-followed by curl :)
    if (!$follow_allowed) {
      //if REDIRECT URL is found in HEADER
      if (empty($redirURL)) {
        if (!empty($status['redirect_url'])) {
          $redirURL = $status['redirect_url'];
        }
      }
      //if REDIRECT URL is found in RESPONSE
      if (empty($redirURL)) {
        preg_match('/(Location:|URI:)(.*?)(\r|\n)/si', $data, $m);
        if (!empty($m[2])) {
          $redirURL = $m[2];
        }
      }
      //if REDIRECT URL is found in OUTPUT
      if (empty($redirURL)) {
        preg_match('/moved\s\<a(.*?)href\=\"(.*?)\"(.*?)here\<\/a\>/si', $data, $m);
        if (!empty($m[1])) {
          $redirURL = $m[1];
        }
      }
      //if URL found, then re-use this function again, for the found url
      if (!empty($redirURL)) {
        $t = debug_backtrace();
        return call_user_func($t[0]["function"], trim($redirURL), $post_paramtrs);
      }
    }
  }
  // if not redirected,and nor "status 200" page, then error..
  elseif($status['http_code'] != 200) {
    $data = "ERRORCODE22 with $url<br/><br/>Last status codes:".json_encode($status).
    "<br/><br/>Last data got:$data";
  }
  return ($return_full_array ? array('data' => $data, 'info' => $status) : $data);
}
