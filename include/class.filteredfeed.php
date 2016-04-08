<?php

class FilteredFeed
{
  // Property declaration
  private $authors       = null;
  private $categories    = null;
  private $content       = null;
  private $date          = null;
  private $entry         = null;
  private $feed_format   = null;
  private $filter        = null;
  private $global_filter = null;
  private $id            = null;
  private $is_debug      = null;
  private $link          = null;
  private $skip          = null;
  private $summary       = null;
  private $thumbnail     = null;
  private $title         = null;

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
    // it is very likely to be a permalink which we'll usein set_link()
    $this->set_id();
    $this->set_link();
    $this->set_title();
    $this->set_thumbnail();
    $this->set_authors();
    $this->set_categories();
    $this->set_summary();
    $this->set_content();
  }

  public function debug_filter()
  {
    $array = $this->get_filter();

    echo '--------------------------------------------------------------------------------------------------------------' . PHP_EOL;
    echo 'Title Filter:              ';
    if(isset($array['title'])) { echo get_array_as_string($array['title']); }
    echo PHP_EOL;
    echo 'Link Filter:               ';
    if(isset($array['link'])) { echo get_array_as_string($array['link']); }
    echo PHP_EOL;
    echo 'Author Filter:             ';
    if(isset($array['author'])) { echo get_array_as_string($array['author']); }
    echo PHP_EOL;
    echo 'Category Filter:           ';
    if(isset($array['category'])) { echo get_array_as_string($array['category']); }
    echo PHP_EOL;
    echo 'Content Filter:            ';
    if(isset($array['content'])) { echo get_array_as_string($array['content']); }
    echo PHP_EOL;
    echo PHP_EOL;
    if ($this->get_skip())
    {
      echo 'FILTER OUT' . PHP_EOL;
    }
    else
    {
      echo 'DO NOT FILTER' . PHP_EOL;
    }
    echo '--------------------------------------------------------------------------------------------------------------';
  }

  public function set_authors()
  {
    $authors = Array();

    if ($this->get_entry()->get_authors())
    {
      // Flatten the authors
      foreach ($this->get_entry()->get_authors() as $author)
      {
        if ($author->get_name())
        {
          $authors[] = $author->get_name();
        }
        if ($author->get_email())
        {
          $authors[] = $author->get_email();
        }
      }
    }

    // Filter out according to author filter
    if ($authors = cleanArray($authors))
    {
      foreach ($authors as $author)
      {
        if($this->filter_keep($author, 'author'))
        {
          $this->set_skip(true);
          return;
        }
        if ($this->filter_out($author, 'author'))
        {
          $this->set_skip(true);
          return;
        }
      }
    }

    $this->authors = $authors;
  }

  public function set_categories()
  {
    $categories = Array();

    // If we don't have categories defined then generate some based on the link and title
    if ($this->get_entry()->get_categories() === null)
    {
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
      */

      // Use part of the title to generate categories
      $categories_from_title = Array();
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
    }
    // Use the entry categories
    else
    {
      // Flatten the categories
      foreach ($this->get_entry()->get_categories() as $category)
      {
        if ($category->get_term())
        {
          $categories[] = $category->get_term();
        }
        if ($category->get_label())
        {
          $categories[] = $category->get_label();
        }
      }
    }

    // Filter out according to category filter
    if ($categories = cleanArray($categories, 'strtolower'))
    {
      foreach ($categories as $category)
      {
        if($this->filter_keep($category, 'category'))
        {
          $this->set_skip(true);
          return;
        }
        if ($this->filter_out($category, 'category'))
        {
          $this->set_skip(true);
          return;
        }
      }
    }

    $this->categories = $categories;
  }

  public function set_content()
  {
    $content = null;

    if ($this->get_entry()->get_content(true))
    {
      $content = $this->get_entry()->get_content(true);

      //$content = html_entity_decode($content);

      // Remove tracking links if present
      $content = preg_replace('#'.'<br clear="all">.*'.'#imu', '', $content);
      $content = preg_replace('#'.'<div.*?></div>'.'#imu', '', $content);
    }

    $this->content = $content;
  }

  public function set_date()
  {
    if ($this->feed_format == 'ATOM')
    {
      $date_format = DATE_ATOM;
    }
    elseif ($this->feed_format == 'RSS')
    {
      $date_format = DATE_RSS;
    }
    
    if ($date = $this->get_entry()->get_updated_gmdate($date_format))
    {
      $this->date = $date;
    }
    elseif ($date = $this->get_entry()->get_gmdate($date_format))
    {
      $this->date = $date;
    }
    else
    {
      $this->date = gmdate($date_format);
    }
  }

  public function set_skip($skip = false)
  {
    $this->skip = $skip;
  }

  public function set_entry($entry = null)
  {
    $this->entry = $entry;
  }

  public function set_filter($filter = null)
  {
    $this->filter = $filter;
  }

  public function set_global_filter($global_filter = null)
  {
    $this->global_filter = $global_filter;
  }

  public function set_id()
  {
    $id = $this->get_entry()->get_id();

    if (! filter_var ($id, FILTER_VALIDATE_URL) === false)
    {
      $id = urldecode($id);

      $scheme = 'http';
      if (after_last($scheme, $id))
      {
        $id = $scheme . after_last($scheme, $id);
      }

      if (before_last('#', $id))
      {
        $id = before_last('#', $id);
      }
    }

    $this->id = $id;
  }

  public function set_link()
  {
    // Check if the ID is a link and use it, otherwise use the regular link
    if (! filter_var ($this->get_id(), FILTER_VALIDATE_URL) === false)
    {
      $link = $this->get_id();
    }
    else
    {
      $feedburner = $this->get_entry()->get_item_tags('http://rssnamespace.org/feedburner/ext/1.0','origLink');

      if (isset($feedburner))
      {
        $link = $feedburner[0] ['data'];
      }
      else
      {
        $link = urldecode($this->get_entry()->get_link());
      }

      $scheme = 'http';
      if (after_last($scheme, $link))
      {
        $link = $scheme . after_last($scheme, $link);
      }

      if (before_last('#', $link))
      {
        $link = before_last('#', $link);
      }
    }

    // Filter out according to link filter
    if($this->filter_keep($link, 'link'))
    {
      $this->set_skip(true);
      return;
    }
    if ($this->filter_out($link, 'link'))
    {
      $this->set_skip(true);
      return;
    }

    $this->link = $link;
  }

  public function set_summary()
  {
    $summary = null;

    if ($this->get_entry()->get_description(true))
    {
      $summary = $this->get_entry()->get_description(true);

      //$summary = html_entity_decode($summary);

      // Remove tracking links if present
      $summary = preg_replace('#'.'<br clear="all">.*'.'#imu', '', $summary);
      $summary = preg_replace('#'.'<div.*?></div>'.'#imu', '', $summary);

      // Filter out if there is no text summary
      if (!preg_match('#'.'[a-z]+'.'#imu', strip_tags($summary)) || $summary == null)
      {
        $this->set_skip(true);
        return;
      }

      // Filter out according to content filter
      if($this->filter_keep($summary, 'content'))
      {
        $this->set_skip(true);
        return;
      }
      if ($this->filter_out($summary, 'content'))
      {
        $this->set_skip(true);
        return;
      }
    }

    $this->summary = $summary;
  }

  // TODO: Thumbnails - solve issues
  public function set_thumbnail()
  {
    return;
    // Look in the entry enclosure for a thumnail
    if ($enclosure = $entry->get_enclosure())
    {
      // Get the thumbnail if it is available
      foreach ((array) $enclosure->get_thumbnails() as $thumbnail)
      {
        $this->thumbnail = urldecode($thumbnail);
      }
      /*
    // Get the image from url if it is available
    foreach ((array) $enclosure->get_url() as $url)
    {
    echo $enclosure->get_type();
    echo $enclosure->get_link();
    $this->thumbnail = $url;
    return;
  }
  */
    } else if ($entry->get_content() != null)
    // If there is no entry enclosure then try to extract image from content
    {
      //This regex selects the images in the SRC attribute of IMG elements
      preg_match('#'.'<img [a-z0-9]*[^<>]*src=(["\'])([^<>]*?)\1[a-z0-9]*[^<>]*>'.'#imu', $entry->get_content(), $matches);
      if ($matches)
      {
        $this->thumbnail = urldecode($matches[2]);
      }
    }
  }

  public function set_title()
  {
    // Filter out according to title filter
    if ($this->title = $this->get_entry()->get_title())
    {
      if($this->filter_keep($this->title, 'title'))
      {
        $this->set_skip(true);
        return;
      }
      if ($this->filter_out($this->title, 'title'))
      {
        $this->set_skip(true);
        return;
      }
    }
  }

  public function get_authors()
  {
    if ($this->authors !== null)
    {
      return $this->authors;
    } else {
      return null;
    }
  }

  public function get_categories()
  {
    if ($this->categories !== null)
    {
      return $this->categories;
    } else {
      return null;
    }
  }

  public function get_content()
  {
    if ($this->content !== null)
    {
      return $this->content;
    } else {
      return null;
    }
  }

  public function get_date()
  {
    if ($this->date !== null)
    {
      return $this->date;
    } else {
      return null;
    }
  }

  public function get_skip()
  {
    if ($this->skip !== null)
    {
      return $this->skip;
    } else {
      return null;
    }
  }

  public function get_entry()
  {
    if ($this->entry !== null)
    {
      return $this->entry;
    } else {
      return null;
    }
  }

  private function get_filter()
  {
    if ($this->filter !== null)
    {
      return $this->filter;
    } else {
      return null;
    }
  }

  private function get_global_filter()
  {
    if ($this->global_filter !== null)
    {
      return $this->global_filter;
    } else {
      return null;
    }
  }

  public function get_id()
  {
    if ($this->id !== null)
    {
      return $this->id;
    } else {
      return null;
    }
  }

  public function get_link()
  {
    if ($this->link !== null)
    {
      return $this->link;
    } else {
      return null;
    }
  }

  public function get_summary()
  {
    if ($this->summary !== null)
    {
      return $this->summary;
    } else {
      return null;
    }
  }

  public function get_thumbnail()
  {
    if ($this->thumbnail !== null)
    {
      return $this->thumbnail;
    } else {
      return null;
    }
  }

  public function get_title()
  {
    if ($this->title !== null)
    {
      return $this->title;
    } else {
      return null;
    }
  }

  /**
* Returns true when the element matches a filter
*/
  private function filter_out($entry, $element)
  {
    $array = $this->get_filter();

    // Decode HTML entities
    $entry = html_entity_decode($entry);

    // Remove all HTML tags
    $entry = strip_tags($entry);

    // Remove accented characters
    $entry = remove_accents($entry);

    // Apply feed filters
    if(isset($array[$element]))
    {
      $filters = $array[$element];

      foreach ($filters as $key => $filter)
      {
        if ($key == 'starts')
        {
          $regex_starts = '^';
          $regex_ends   = '';
        }
        elseif ($key == 'contains')
        {
          $regex_starts = '\b';
          $regex_ends   = '\b';
        }
        elseif ($key == 'ends')
        {
          $regex_starts = '';
          $regex_ends = '$';
        }
        elseif ($key == 'regex')
        {
          $regex_starts = '';
          $regex_ends   = '';
        }

        foreach ($filter as $value)
        {
          if (preg_match('#' . $regex_starts . $value . $regex_ends . '#imu', $entry) !== 0)
          {
            return true;
          }
        }
      }
    }

    $array = $this->get_global_filter();

    // Apply global
    if(isset($array[$element]))
    {
      $filters = $array[$element];

      foreach ($filters as $key => $filter)
      {
        if ($key == 'starts')
        {
          $regex_starts = '^';
          $regex_ends   = '';
        }
        elseif ($key == 'contains')
        {
          $regex_starts = '\b';
          $regex_ends   = '\b';
        }
        elseif ($key == 'ends')
        {
          $regex_starts = '';
          $regex_ends = '$';
        }
        elseif ($key == 'regex')
        {
          $regex_starts = '';
          $regex_ends   = '';
        }

        foreach ($filter as $value)
        {
          if (preg_match('#' . $regex_starts . $value . $regex_ends . '#imu', $entry) !== 0)
          {
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
  private function filter_keep($entry, $element)
  {
    $array = $this->get_filter();

    // Decode HTML entities
    $entry = html_entity_decode($entry);

    // Remove all HTML tags
    $entry = strip_tags($entry);

    // Remove accented characters
    $entry = remove_accents($entry);

    // Apply feed filters
    if(isset($array['keep'][$element]))
    {
      $filters = $array['keep'][$element];

      foreach ($filters as $key => $filter)
      {
        if ($key == 'starts')
        {
          $regex_starts = '^';
          $regex_ends   = '';
        }
        elseif ($key == 'contains')
        {
          $regex_starts = '\b';
          $regex_ends   = '\b';
        }
        elseif ($key == 'ends')
        {
          $regex_starts = '';
          $regex_ends = '$';
        }
        elseif ($key == 'regex')
        {
          $regex_starts = '';
          $regex_ends   = '';
        }

        foreach ($filter as $value)
        {
          if (preg_match('#' . $regex_starts . $value . $regex_ends . '#imu', $entry) !== 0)
          {
            return false;
          }
        }
      }
      return true;
    }
    return false;
  }
}
