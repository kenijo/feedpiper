<?php

class Filter
{
  // Property declaration
  private $authors          = null;
  private $categories       = null;
  private $content          = null;
  private $date             = null;
  private $enclosureLength = null;
  private $enclosureLink   = null;
  private $enclosureType   = null;
  private $entry            = null;
  private $feedFilter      = null;
  private $feedFormat      = null;
  private $blacklist = null;
  private $whitelist = null;
  private $globalBlacklist = null;
  private $id               = null;
  private $link             = null;
  private $skip             = null;
  private $summary          = null;
  private $title            = null;

  function __construct($feedFormat)
  {
    $this->feedFormat = $feedFormat;
  }

  public function filter($entry)
  {
    $this->setEntry($entry);
    $this->setSkip(false);

    $this->setDate();
    // Set the id first because if the id happens to be a link then
    // it is very likely to be a permalink which we'll use in set_link()
    $this->setId();
    $this->setLink();
    $this->setTitle();
    $this->setAuthors();
    $this->setCategories();
    $this->setSummary();
    $this->setContent();
    $this->setEnclosure();
  }

  public function debug()
  {
    $array_entry_whitelist  = $this->getWhitelist();
    $array_entry_blacklist  = $this->getBlacklist();
    $arrayGlobalBlacklist = $this->getGlobalBlacklist();

    echo '########################################################################################################################' . PHP_EOL;
    if (isset($array_entry_whitelist)) {
      if (isset($array_entry_whitelist['title'])) {
        echo 'Whitelist Title:           ';
        echo convertArrayToCommaSeparatedString($array_entry_whitelist['title']);
        echo PHP_EOL;
      }
      if (isset($array_entry_whitelist['author'])) {
        echo 'Whitelist Author:          ';
        echo convertArrayToCommaSeparatedString($array_entry_whitelist['author']);
        echo PHP_EOL;
      }
      if (isset($array_entry_whitelist['category'])) {
        echo 'Whitelist Category:        ';
        echo convertArrayToCommaSeparatedString($array_entry_whitelist['category']);
        echo PHP_EOL;
      }
      if (isset($array_entry_whitelist['content'])) {
        echo 'Whitelist Content:         ';
        echo convertArrayToCommaSeparatedString($array_entry_whitelist['content']);
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
        echo convertArrayToCommaSeparatedString($array_entry_blacklist['title']);
        echo PHP_EOL;
      }
      if (isset($array_entry_blacklist['author'])) {
        echo 'Blacklist Author:          ';
        echo convertArrayToCommaSeparatedString($array_entry_blacklist['author']);
        echo PHP_EOL;
      }
      if (isset($array_entry_blacklist['category'])) {
        echo 'Blacklist Category:        ';
        echo convertArrayToCommaSeparatedString($array_entry_blacklist['category']);
        echo PHP_EOL;
      }
      if (isset($array_entry_blacklist['content'])) {
        echo 'Blacklist Content:         ';
        echo convertArrayToCommaSeparatedString($array_entry_blacklist['content']);
        echo PHP_EOL;
      }
      echo PHP_EOL;
    } else {
      echo "No blacklist";
      echo PHP_EOL;
      echo PHP_EOL;
    }

    if (isset($arrayGlobalBlacklist)) {
      if (isset($arrayGlobalBlacklist['title'])) {
        echo 'Global Blacklist Title:           ';
        echo convertArrayToCommaSeparatedString($arrayGlobalBlacklist['title']);
        echo PHP_EOL;
      }
      if (isset($arrayGlobalBlacklist['author'])) {
        echo 'Global Blacklist Author:          ';
        echo convertArrayToCommaSeparatedString($arrayGlobalBlacklist['author']);
        echo PHP_EOL;
      }
      if (isset($arrayGlobalBlacklist['category'])) {
        echo 'Global Blacklist Category:        ';
        echo convertArrayToCommaSeparatedString($arrayGlobalBlacklist['category']);
        echo PHP_EOL;
      }
      if (isset($arrayGlobalBlacklist['content'])) {
        echo 'Global Blacklist Content:         ';
        echo convertArrayToCommaSeparatedString($arrayGlobalBlacklist['content']);
        echo PHP_EOL;
      }
      echo PHP_EOL;
    } else {
      echo "No global blacklist";
      echo PHP_EOL;
      echo PHP_EOL;
    }

    if ($this->getSkip()) {
      echo 'REMOVE THIS FEED ENTRY' . PHP_EOL;
    } else {
      echo 'KEEP THIS FEED ENTRY' . PHP_EOL;
    }
    echo '########################################################################################################################';
  }

  public function setAuthors()
  {
    $authors = [];

    if ($this->getEntry()->get_authors()) {
      // Flatten the authors
      foreach ($this->getEntry()->get_authors() as $author) {
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
        $this->setSkip(true);
        return;
      }
      if ($this->blacklist($author, 'author')) {
        $this->setSkip(true);
        return;
      }
    }
  }

  public function setCategories()
  {
    $categories = [];
    // If we don't have categories defined then generate some based on the link and title
    if ($this->getEntry()->get_categories() === null) {

      // START - Uncomment this code if you want to generate categories for feeds who don't have
      // any category defined, based on link and/or title.
      $categories_from_link  = [];
      $categories_from_title = [];

      // Use the link to generate categories
      if ($link = $this->getLink())
      {
        $link                 = urldecode($link);
        $link                 = substr($link, strpos($link, '//')+2);
        $link                 = substr($link, strpos($link, '/')+1);
        $link                 = substr($link, 0, strrpos($link, '/'));
        $link                 = parse_url($link)['path'];
        $link                 = preg_replace('#'.'[[:punct:]]'.'#imu', ' ', $link);
        $categories_from_link = explode(' ', $link);
      }

      // Use part of the title to generate categories
      /*
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
      */
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

      // END - Uncomment this code if you want to generate categories for feeds who don't have
      // any category defined, based on link and/or title.

    }
    // Use the entry categories
    else {
      // Flatten the categories
      foreach ($this->getEntry()->get_categories() as $category) {
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
        $this->setSkip(true);
        return;
      }
      if ($this->blacklist($category, 'category')) {
        $this->setSkip(true);
        return;
      }
    }
  }

  public function setContent()
  {
    $content = null;

    if ($this->getEntry()->get_content(true)) {
      $content = $this->getEntry()->get_content(true);

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

  public function setDate()
  {
    if ($this->feedFormat == 'ATOM') {
      $date_format = DATE_ATOM;
    } elseif ($this->feedFormat == 'RSS') {
      $date_format = DATE_RSS;
    }

    if ($date = $this->getEntry()->get_updated_gmdate($date_format)) {
      $this->date = $date;
    } elseif ($date = $this->getEntry()->get_gmdate($date_format)) {
      $this->date = $date;
    } else {
      $this->date = gmdate($date_format);
    }
  }

  private function setEnclosure()
  {
    // Look for an entry enclosure
    $enclosure = $this->entry->get_enclosure();

    if ($enclosure) {
      // Strip '?#' from the end of the link
      $link = beforeLast('?#', $enclosure->get_link());

      // If link == '//'
      if ($link == '//') {
        // This regex selects the images in the SRC attribute of IMG elements
        if ($this->getContent() != null) {
          $description = $this->getContent();
        } else {
          $description = $this->getSummary();
        }
        preg_match('#' . '<img [a-z0-9]*[^<>]*src=(["\'])([^<>]*?)\1[a-z0-9]*[^<>]*>' . '#imu', $description, $matches);

        if ($matches) {
          // Then we use the first image found in the content or in the summary
          $link = urldecode($matches[2]);
        } else {
          // Then we nullify the link
          $link = null;
        }
        $this->setEnclosureLink($link);
      } else {
        $this->setEnclosureLink($link);
        $this->setEnclosureLength($enclosure->get_length());
        $this->setEnclosureType($enclosure->get_type());
      }
    }
  }

  public function setEnclosureLength($entry = null)
  {
    $this->enclosureLength = $entry;
  }

  public function setEnclosureLink($entry = null)
  {
    $this->enclosureLink = $entry;
  }

  public function setEnclosureType($entry = null)
  {
    $this->enclosureType = $entry;
  }

  public function setEntry($entry = null)
  {
    $this->entry = $entry;
  }

  public function setWhitelist($whitelist = null)
  {
    $this->whitelist = $whitelist;
  }

  public function setBlacklist($blacklist = null)
  {
    $this->blacklist = $blacklist;
  }

  public function setGlobalBlacklist($global_blacklist = null)
  {
    $this->globalBlacklist = $global_blacklist;
  }

  public function setId()
  {
    $id = $this->getEntry()->get_id();

    if (!filter_var($id, FILTER_VALIDATE_URL) === false) {
      $id = urldecode($id);

      $scheme = 'http';
      if (afterLast($scheme, $id)) {
        $id = $scheme . afterLast($scheme, $id);
      }

      if (beforeLast('#', $id)) {
        $id = beforeLast('#', $id);
      }
    }

    $this->id = $id;
  }

  public function setLink()
  {
    // Check if the ID is a link and use it, otherwise use the regular link
    if (!filter_var($this->getId(), FILTER_VALIDATE_URL) === false) {
      $link = $this->getId();
    } else {
      $feedburner = $this->getEntry()->get_item_tags('http://rssnamespace.org/feedburner/ext/1.0', 'origLink');

      if (isset($feedburner)) {
        $link = $feedburner[0]['data'];
      } else {
        $link = urldecode($this->getEntry()->get_link());
      }

      if (beforeLast('#', $link)) {
        $link = beforeLast('#', $link);
      }
    }

    $this->link = $link;
  }

  public function setSkip($skip = false)
  {
    $this->skip = $skip;
  }

  public function setSummary()
  {
    $summary = null;

    if ($this->getEntry()->get_description(true)) {
      $summary = $this->getEntry()->get_description(true);

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
      $this->setSkip(true);
      return;
    }
    if ($this->blacklist($this->summary, 'content')) {
      $this->setSkip(true);
      return;
    }
  }

  public function setTitle()
  {
    // Filter according to title filter
    if ($this->title = $this->getEntry()->get_title()) {
      if($this->whitelist($this->title, 'title'))
      {
        $this->setSkip(true);
        return;
      }
      if ($this->blacklist($this->title, 'title')) {
        $this->setSkip(true);
        return;
      }
    }
  }

  public function getAuthors()
  {
    if ($this->authors !== null) {
      return $this->authors;
    } else {
      return null;
    }
  }

  public function getCategories()
  {
    if ($this->categories !== null) {
      return $this->categories;
    } else {
      return null;
    }
  }

  public function getContent()
  {
    if ($this->content !== null) {
      return $this->content;
    } else {
      return null;
    }
  }

  public function getDate()
  {
    if ($this->date !== null) {
      return $this->date;
    } else {
      return null;
    }
  }

  public function getEnclosureLength()
  {
    if ($this->enclosureLength !== null) {
      return $this->enclosureLength;
    } else {
      return null;
    }
  }

  public function getEnclosureLink()
  {
    if ($this->enclosureLink !== null) {
      return $this->enclosureLink;
    } else {
      return null;
    }
  }

  public function getEnclosureType()
  {
    if ($this->enclosureType !== null) {
      return $this->enclosureType;
    } else {
      return null;
    }
  }

  public function getEntry()
  {
    if ($this->entry !== null) {
      return $this->entry;
    } else {
      return null;
    }
  }

  private function getWhitelist()
  {
    if ($this->whitelist !== null) {
      return $this->whitelist;
    } else {
      return null;
    }
  }

  private function getBlacklist()
  {
    if ($this->blacklist !== null) {
      return $this->blacklist;
    } else {
      return null;
    }
  }

  private function getGlobalBlacklist()
  {
    if ($this->globalBlacklist !== null) {
      return $this->globalBlacklist;
    } else {
      return null;
    }
  }

  public function getId()
  {
    if ($this->id !== null) {
      return $this->id;
    } else {
      return null;
    }
  }

  public function getLink()
  {
    if ($this->link !== null) {
      return $this->link;
    } else {
      return null;
    }
  }

  public function getSkip()
  {
    if ($this->skip !== null) {
      return $this->skip;
    } else {
      return null;
    }
  }

  public function getSummary()
  {
    if ($this->summary !== null) {
      return $this->summary;
    } else {
      return null;
    }
  }

  public function getTitle()
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
    $array = $this->getBlacklist();

    // Decode HTML entities
    $entry = html_entity_decode($entry);

    // Remove all HTML tags
    $entry = strip_tags($entry);

    // Remove accented characters
    $entry = removeAccents($entry);

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

    $array = $this->getGlobalBlacklist();

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
    $array = $this->getWhitelist();

    // Decode HTML entities
    $entry = html_entity_decode($entry);

    // Remove all HTML tags
    $entry = strip_tags($entry);

    // Remove accented characters
    $entry = removeAccents($entry);

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
