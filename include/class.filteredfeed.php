<?php

class FilteredFeed
{
  // Property declaration
  private $authors          = null;
  private $categories       = null;
  private $content          = null;
  private $date             = null;
  private $enclosure_length = null;
  private $enclosure_link   = null;
  private $enclosure_type   = null;
  private $entry            = null;
  private $feed_filter      = null;
  private $feed_format      = null;
  private $global_blacklist = null;
  private $id               = null;
  private $link             = null;
  private $skip             = null;
  private $summary          = null;
  private $title            = null;

  function __construct($feed_format)
  {
    $this->feed_format = $feed_format;
  }

  public function filter($entry)
  {
    $this->set_entry($entry);
    $this->set_skip(false);

    $this->set_date();
    // Set the id first because if the id happens to be a link then
    // it is very likely to be a permalink which we'll use in set_link()
    $this->set_id();
    $this->set_link();
    $this->set_title();
    $this->set_authors();
    $this->set_categories();
    $this->set_summary();
    $this->set_content();
    $this->set_enclosure();
  }

  public function debug_filter()
  {
    $array_entry_whitelist  = $this->get_entry_whitelist();
    $array_entry_blacklist  = $this->get_entry_blacklist();
    $array_global_blacklist = $this->get_global_blacklist();

    echo '########################################################################################################################' . PHP_EOL;
    if (isset($array_entry_whitelist)) {  
      if (isset($array_entry_whitelist['title'])) {
        echo 'Whitelist Title:           ';
        echo get_array_as_string($array_entry_whitelist['title']);
        echo PHP_EOL;
      }
      if (isset($array_entry_whitelist['author'])) {
        echo 'Whitelist Author:          ';
        echo get_array_as_string($array_entry_whitelist['author']);
        echo PHP_EOL;
      }
      if (isset($array_entry_whitelist['category'])) {
        echo 'Whitelist Category:        ';
        echo get_array_as_string($array_entry_whitelist['category']);
        echo PHP_EOL;
      }
      if (isset($array_entry_whitelist['content'])) {
        echo 'Whitelist Content:         ';
        echo get_array_as_string($array_entry_whitelist['content']);
        echo PHP_EOL;
      }
      echo PHP_EOL;
    } else {
      echo "No whitelist";
      echo PHP_EOL;
      echo PHP_EOL;
    }
    
    if (isset($array_entry_blacklist)) {  
      if (isset($array_entry_blacklist['title'])) {
        echo 'Blacklist Title:           ';
        echo get_array_as_string($array_entry_blacklist['title']);
        echo PHP_EOL;
      }
      if (isset($array_entry_blacklist['author'])) {
        echo 'Blacklist Author:          ';
        echo get_array_as_string($array_entry_blacklist['author']);
        echo PHP_EOL;
      }
      if (isset($array_entry_blacklist['category'])) {
        echo 'Blacklist Category:        ';
        echo get_array_as_string($array_entry_blacklist['category']);
        echo PHP_EOL;
      }
      if (isset($array_entry_blacklist['content'])) {
        echo 'Blacklist Content:         ';
        echo get_array_as_string($array_entry_blacklist['content']);
        echo PHP_EOL;
      }
      echo PHP_EOL;
    } else {
      echo "No blacklist";
      echo PHP_EOL;
      echo PHP_EOL;
    }
    
    if (isset($array_global_blacklist)) {  
      if (isset($array_global_blacklist['title'])) {
        echo 'Global Blacklist Title:           ';
        echo get_array_as_string($array_global_blacklist['title']);
        echo PHP_EOL;
      }
      if (isset($array_global_blacklist['author'])) {
        echo 'Global Blacklist Author:          ';
        echo get_array_as_string($array_global_blacklist['author']);
        echo PHP_EOL;
      }
      if (isset($array_global_blacklist['category'])) {
        echo 'Global Blacklist Category:        ';
        echo get_array_as_string($array_global_blacklist['category']);
        echo PHP_EOL;
      }
      if (isset($array_global_blacklist['content'])) {
        echo 'Global Blacklist Content:         ';
        echo get_array_as_string($array_global_blacklist['content']);
        echo PHP_EOL;
      }
      echo PHP_EOL;
    } else {
      echo "No global blacklist";
      echo PHP_EOL;
      echo PHP_EOL;
    }
    
    if ($this->get_skip()) {
      echo 'REMOVE THIS FEED ENTRY' . PHP_EOL;
    } else {
      echo 'KEEP THIS FEED ENTRY' . PHP_EOL;
    }
    echo '########################################################################################################################';
  }

  public function set_authors()
  {
    $authors = array();

    if ($this->get_entry()->get_authors()) {
      // Flatten the authors
      foreach ($this->get_entry()->get_authors() as $author) {
        if ($author->get_name()) {
          $authors[] = $author->get_name();
        }
        if ($author->get_email()) {
          $authors[] = $author->get_email();
        }
      }
    }

    $this->authors = cleanArray($authors);

    // Filter according to author filter
    foreach ($this->authors as $author) {
      if($this->whitelist($author, 'author'))
      {
        $this->set_skip(true);
        return;
      }
      if ($this->blacklist($author, 'author')) {
        $this->set_skip(true);
        return;
      }
    }
  }

  public function set_categories()
  {
    $categories = array();
    // If we don't have categories defined then generate some based on the link and title
    if ($this->get_entry()->get_categories() === null) {
      /*
      // Uncomment this code if you want to generate categories for feeds who don't have
      // any category defined, based on link and/or title. 

      // Use the link to generate categories
      $categories_from_link = Array();
      /*
      if ($link = $this->get_link())
      {
        $link                 = urldecode($link);
        $link                 = parse_url($link)['path'];
        $link                 = preg_replace('#'.'[[:punct:]]'.'#imu', ' ', $link);
        $categories_from_link = explode(' ', $link);
      }

      // Use part of the title to generate categories
      $categories_from_title = Array();
      if ($title = $this->get_title())
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

        $categories_from_title = array_map('trim', explode(' ', $title));
      }

      $categories = array_merge($categories_from_link, $categories_from_title);

      // Get rid of values that are null, empty, false, < 3 and numbers only
      $categories = array_filter($categories,
                                 function ($key) use (&$value)
                                 {
                                   if (strlen($key) > 3 && !preg_match('#'.'\d+'.'#imu', $key))
                                   {
                                     return $key;
                                   }
                                 });
      */
    }
    // Use the entry categories
    else {
      // Flatten the categories
      foreach ($this->get_entry()->get_categories() as $category) {
        if ($category->get_term()) {
          $categories[] = $category->get_term();
        }
        if ($category->get_label()) {
          $categories[] = $category->get_label();
        }
      }
    }

    $this->categories = cleanArray($categories, 'strtolower');

    // Filter according to category filter
    foreach ($this->categories as $category) {
      if($this->whitelist($category, 'category'))
      {
        $this->set_skip(true);
        return;
      }    
      if ($this->blacklist($category, 'category')) {
        $this->set_skip(true);
        return;
      }
    }
  }

  public function set_content()
  {
    $content = null;

    if ($this->get_entry()->get_content(true)) {
      $content = $this->get_entry()->get_content(true);

      //$content = html_entity_decode($content);

      // Remove tracking links if present
      $content = preg_replace('#' . '<br clear="all">.*' . '#imu', '', $content);
      $content = preg_replace('#' . '<div.*?></div>' . '#imu', '', $content);

      // Remove left over spaces
      $content = preg_replace('#' . '(\s)+' . '#imu', ' ', $content);
      $content = trim($content);
    }

    $this->content = $content;
  }

  public function set_date()
  {
    if ($this->feed_format == 'ATOM') {
      $date_format = DATE_ATOM;
    } elseif ($this->feed_format == 'RSS') {
      $date_format = DATE_RSS;
    }

    if ($date = $this->get_entry()->get_updated_gmdate($date_format)) {
      $this->date = $date;
    } elseif ($date = $this->get_entry()->get_gmdate($date_format)) {
      $this->date = $date;
    } else {
      $this->date = gmdate($date_format);
    }
  }

  private function set_enclosure()
  {
    // Look for an entry enclosure
    $enclosure = $this->entry->get_enclosure();

    if ($enclosure) {
      // Strip '?#' from the end of the link
      $link = before_last('?#', $enclosure->get_link());

      // If link == '//'
      if ($link == '//') {
        // This regex selects the images in the SRC attribute of IMG elements
        if ($this->get_content() != null) {
          $description = $this->get_content();
        } else {
          $description = $this->get_summary();
        }
        preg_match('#' . '<img [a-z0-9]*[^<>]*src=(["\'])([^<>]*?)\1[a-z0-9]*[^<>]*>' . '#imu', $description, $matches);

        if ($matches) {
          // Then we use the first image found in the content or in the summary
          $link = urldecode($matches[2]);
        } else {
          // Then we nullify the link
          $link = null;
        }
        $this->set_enclosure_link($link);
      } else {
        $this->set_enclosure_link($link);
        $this->set_enclosure_length($enclosure->get_length());
        $this->set_enclosure_type($enclosure->get_type());
      }
    }
  }

  public function set_enclosure_length($entry = null)
  {
    $this->enclosure_length = $entry;
  }

  public function set_enclosure_link($entry = null)
  {
    $this->enclosure_link = $entry;
  }

  public function set_enclosure_type($entry = null)
  {
    $this->enclosure_type = $entry;
  }

  public function set_entry($entry = null)
  {
    $this->entry = $entry;
  }

  public function set_entry_whitelist($whitelist = null)
  {
    $this->whitelist = $whitelist;
  }
  
  public function set_entry_blacklist($blacklist = null)
  {
    $this->blacklist = $blacklist;
  }

  public function set_global_blacklist($global_blacklist = null)
  {
    $this->global_blacklist = $global_blacklist;
  }

  public function set_id()
  {
    $id = $this->get_entry()->get_id();

    if (!filter_var($id, FILTER_VALIDATE_URL) === false) {
      $id = urldecode($id);

      $scheme = 'http';
      if (after_last($scheme, $id)) {
        $id = $scheme . after_last($scheme, $id);
      }

      if (before_last('#', $id)) {
        $id = before_last('#', $id);
      }
    }

    $this->id = $id;
  }

  public function set_link()
  {
    // Check if the ID is a link and use it, otherwise use the regular link
    if (!filter_var($this->get_id(), FILTER_VALIDATE_URL) === false) {
      $link = $this->get_id();
    } else {
      $feedburner = $this->get_entry()->get_item_tags('http://rssnamespace.org/feedburner/ext/1.0', 'origLink');

      if (isset($feedburner)) {
        $link = $feedburner[0]['data'];
      } else {
        $link = urldecode($this->get_entry()->get_link());
      }

      if (before_last('#', $link)) {
        $link = before_last('#', $link);
      }
    }

    $this->link = $link;
  }

  public function set_skip($skip = false)
  {
    $this->skip = $skip;
  }

  public function set_summary()
  {
    $summary = null;

    if ($this->get_entry()->get_description(true)) {
      $summary = $this->get_entry()->get_description(true);

      //$summary = html_entity_decode($summary);

      // Remove tracking links if present
      $summary = preg_replace('#' . '<br clear="all">.*' . '#imu', '', $summary);
      $summary = preg_replace('#' . '<div.*?></div>' . '#imu', '', $summary);

      // Remove left over spaces
      $summary = preg_replace('#' . '(\s)+' . '#imu', ' ', $summary);
      $summary = trim($summary);
    }

    $this->summary = $summary;

    // Filter according to content filter
    if($this->whitelist($this->summary, 'content'))
    {
      $this->set_skip(true);
      return;
    }
    if ($this->blacklist($this->summary, 'content')) {
      $this->set_skip(true);
      return;
    }
  }

  public function set_title()
  {
    // Filter according to title filter
    if ($this->title = $this->get_entry()->get_title()) {
      if($this->whitelist($this->title, 'title'))
      {
        $this->set_skip(true);
        return;
      }
      if ($this->blacklist($this->title, 'title')) {
        $this->set_skip(true);
        return;
      }
    }
  }

  public function get_authors()
  {
    if ($this->authors !== null) {
      return $this->authors;
    } else {
      return null;
    }
  }

  public function get_categories()
  {
    if ($this->categories !== null) {
      return $this->categories;
    } else {
      return null;
    }
  }

  public function get_content()
  {
    if ($this->content !== null) {
      return $this->content;
    } else {
      return null;
    }
  }

  public function get_date()
  {
    if ($this->date !== null) {
      return $this->date;
    } else {
      return null;
    }
  }

  public function get_enclosure_length()
  {
    if ($this->enclosure_length !== null) {
      return $this->enclosure_length;
    } else {
      return null;
    }
  }

  public function get_enclosure_link()
  {
    if ($this->enclosure_link !== null) {
      return $this->enclosure_link;
    } else {
      return null;
    }
  }

  public function get_enclosure_type()
  {
    if ($this->enclosure_type !== null) {
      return $this->enclosure_type;
    } else {
      return null;
    }
  }

  public function get_entry()
  {
    if ($this->entry !== null) {
      return $this->entry;
    } else {
      return null;
    }
  }

  private function get_entry_whitelist()
  {
    if ($this->whitelist !== null) {
      return $this->whitelist;
    } else {
      return null;
    }
  }
  
  private function get_entry_blacklist()
  {
    if ($this->blacklist !== null) {
      return $this->blacklist;
    } else {
      return null;
    }
  }

  private function get_global_blacklist()
  {
    if ($this->global_blacklist !== null) {
      return $this->global_blacklist;
    } else {
      return null;
    }
  }

  public function get_id()
  {
    if ($this->id !== null) {
      return $this->id;
    } else {
      return null;
    }
  }

  public function get_link()
  {
    if ($this->link !== null) {
      return $this->link;
    } else {
      return null;
    }
  }

  public function get_skip()
  {
    if ($this->skip !== null) {
      return $this->skip;
    } else {
      return null;
    }
  }

  public function get_summary()
  {
    if ($this->summary !== null) {
      return $this->summary;
    } else {
      return null;
    }
  }

  public function get_title()
  {
    if ($this->title !== null) {
      return $this->title;
    } else {
      return null;
    }
  }

  /**
   * Returns true when the element matches a filter
   */
  private function blacklist($entry, $element)
  {
    $array = $this->get_entry_blacklist();

    // Decode HTML entities
    $entry = html_entity_decode($entry);

    // Remove all HTML tags
    $entry = strip_tags($entry);

    // Remove accented characters
    $entry = remove_accents($entry);

    // Apply feed filters
    if (isset($array[$element])) {
      $feed_filters = $array[$element];

      foreach ($feed_filters as $key => $feed_filter) {
        if ($key == 'starts') {
          $regex_starts = '^';
          $regex_ends   = '';
        } elseif ($key == 'contains') {
          $regex_starts = '\b';
          $regex_ends   = '\b';
        } elseif ($key == 'ends') {
          $regex_starts = '';
          $regex_ends = '$';
        } elseif ($key == 'regex') {
          $regex_starts = '';
          $regex_ends   = '';
        }

        foreach ($feed_filter as $value) {
          if (preg_match('#' . $regex_starts . $value . $regex_ends . '#imu', $entry) !== 0) {
            return true;
          }
        }
      }
    }

    $array = $this->get_global_blacklist();

    // Apply global
    if (isset($array[$element])) {
      $feed_filters = $array[$element];

      foreach ($feed_filters as $key => $feed_filter) {
        if ($key == 'starts') {
          $regex_starts = '^';
          $regex_ends   = '';
        } elseif ($key == 'contains') {
          $regex_starts = '\b';
          $regex_ends   = '\b';
        } elseif ($key == 'ends') {
          $regex_starts = '';
          $regex_ends = '$';
        } elseif ($key == 'regex') {
          $regex_starts = '';
          $regex_ends   = '';
        }

        foreach ($feed_filter as $value) {
          if (preg_match('#' . $regex_starts . $value . $regex_ends . '#imu', $entry) !== 0) {
            return true;
          }
        }
      }
    }
    return false;
  }

  /**
   * Returns false when the element matches a filter
   */
  private function whitelist($entry, $element)
  {
    $array = $this->get_entry_whitelist();

    // Decode HTML entities
    $entry = html_entity_decode($entry);

    // Remove all HTML tags
    $entry = strip_tags($entry);

    // Remove accented characters
    $entry = remove_accents($entry);

    // Apply feed filters
    if (isset($array[$element])) {
      $feed_filters = $array[$element];

      foreach ($feed_filters as $key => $feed_filter) {
        if ($key == 'starts') {
          $regex_starts = '^';
          $regex_ends   = '';
        } elseif ($key == 'contains') {
          $regex_starts = '\b';
          $regex_ends   = '\b';
        } elseif ($key == 'ends') {
          $regex_starts = '';
          $regex_ends = '$';
        } elseif ($key == 'regex') {
          $regex_starts = '';
          $regex_ends   = '';
        }

        foreach ($feed_filter as $value) {
          if (preg_match('#' . $regex_starts . $value . $regex_ends . '#imu', $entry) !== 0) {
            return false;
          }
        }
      }
      return true;
    }
    return false;
  }
}
