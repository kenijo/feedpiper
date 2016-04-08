<?php

class Feed
{
  // Property declaration
  private $feed_format            = null;
  private $feed_generator_name    = null;
  private $feed_generator_uri     = null;
  private $feed_generator_version = null;
  private $feed_icon              = null;
  private $feed_id                = null;
  private $feed_link              = null;
  private $feed_link_alternate    = null;
  private $feed_logo              = null;
  private $feed_title             = null;
  private $feed_updated           = null;
  private $feed_website_link      = null;

  function __construct($feed_format)
  {
    $this->set_feed_format($feed_format);
  }

  public function open_feed()
  {
    if ($this->feed_format == 'ATOM')
    {
      $this->open_atom_feed();
    }
    elseif ($this->feed_format == 'RSS')
    {
      $this->open_rss_feed();
    }
  }

  public function close_feed()
  {
    if ($this->feed_format == 'ATOM')
    {
      $this->close_atom_feed();
    }
    elseif ($this->feed_format == 'RSS')
    {
      $this->close_rss_feed();
    }
  }

  private function open_atom_feed()
  {
    // Atom Syndication Format
    // http://atomenabled.org/developers/syndication/
    echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
    echo '<feed xmlns:dc="http://purl.org/dc/elements/1.1/"' . PHP_EOL;
    echo '      xmlns:media="http://search.yahoo.com/mrss/"' . PHP_EOL;
    echo '      xmlns="http://www.w3.org/2005/Atom" >' . PHP_EOL;

    // Contains a human readable title for the feed.
    // Often the same as the title of the associated website.
    // This value should not be blank.
    echo '  <title type="text">' . $this->get_feed_title() . '</title>' . PHP_EOL;

    // Contains the link to the original website providing the feed
    echo '  <link type="text/html" title="' . $this->get_feed_title() . '" href="' . $this->get_feed_website_link() . '" rel="related" />' . PHP_EOL;

    // RSS autodiscovery is a technique that makes it possible for web browsers and other software to automatically
    // find a site's RSS feed. Autodiscovery is a great way to inform users that a web site offers a syndication feed.
    // To support autodiscovery, a link element must be added to the header, as shown in the HTML markup below.
    // Replace the href value of the link element with the URL of your RSS feed.
    echo '  <link type="application/atom+xml" title="' . $this->get_feed_title() . '" href="' . $this->get_feed_link_alternate() . '" rel="alternate" />' . PHP_EOL;

    // Identifies a related Web page.
    // The type of relation is defined by the rel attribute.
    // A feed is limited to one alternate per type and hreflang.
    // A feed should contain a link back to the feed itself.
    echo '  <link type="application/atom+xml" title="' . $this->get_feed_title() . '" href="' . $this->get_feed_link() . '" rel="self" />' . PHP_EOL;

    // Identifies a small image which provides iconic visual identification for the feed.
    // Icons should be square.
    echo '  <icon>' . $this->get_feed_icon() . '</icon>' . PHP_EOL;

    //Identifies a larger image which provides visual identification for the feed.
    // Images should be twice as wide as they are tall.
    echo '  <logo>' . $this->get_feed_logo() . '</logo>' . PHP_EOL;

    // Identifies the software used to generate the feed, for debugging and other purposes.
    // Both the uri and version attributes are optional.
    echo '  <generator uri="' . $this->get_feed_generator_uri() . '" version="' . $this->get_feed_generator_version() . '">' . $this->get_feed_generator_name() . '</generator>' . PHP_EOL;

    // Indicates the last time the feed was modified in a significant way.
    // All timestamps in Atom must conform to RFC 3339.
    echo '  <updated>' . $this->get_feed_updated() . '</updated>' . PHP_EOL;

    // Identifies the feed using a universally unique and permanent URI.
    // If you have a long-term, renewable lease on your Internet domain name,
    // then you can feel free to use your website’s address.
    echo '  <id>' . $this->get_feed_link() . '</id>' . PHP_EOL;

    // Separate entries from the header for clarity
    echo PHP_EOL;
  }

  private function open_rss_feed()
  {
    // Atom Syndication Format
    // http://atomenabled.org/developers/syndication/
    echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
    echo '<rss version="2.0"' . PHP_EOL;
    echo '   xmlns:dc="http://purl.org/dc/elements/1.1/"' . PHP_EOL;
    echo '   xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">' . PHP_EOL;

    echo '  <channel>' . PHP_EOL;

    echo '    <title>' . $this->get_feed_title() . '</title>' . PHP_EOL;

    echo '    <link>' . $this->get_feed_website_link() . '</link>' . PHP_EOL;

    echo '    <description>' . $this->get_feed_title() . '</description>' . PHP_EOL;

    echo '    <pubDate>' . $this->get_feed_updated() . '</pubDate>' . PHP_EOL;
    echo '    <lastBuildDate>' . $this->get_feed_updated() . '</lastBuildDate>' . PHP_EOL;

    echo '    <image>' . PHP_EOL;
    echo '      <url>' . $this->get_feed_logo() . '</url>' . PHP_EOL;
    echo '      <title>' . $this->get_feed_title() . '</title>' . PHP_EOL;
    echo '      <link>' . $this->get_feed_website_link() . '</link>' . PHP_EOL;
    echo '    </image>' . PHP_EOL;

    // Separate entries from the header for clarity
    echo PHP_EOL;
  }

  public function debug_feed()
  {
    echo '--------------------------------------------------------------------------------------------------------------' . PHP_EOL;
    echo 'Feed Title:                ' . $this->get_feed_title() . PHP_EOL;
    echo 'Feed Link:                 ' . $this->get_feed_link() . PHP_EOL;
    echo 'Feed Link Alternative:     ' . $this->get_feed_link_alternate() . PHP_EOL;
    echo 'Feed Website Link:         ' . $this->get_feed_website_link() . PHP_EOL;
    echo 'Feed Icon:                 ' . $this->get_feed_icon() . PHP_EOL;
    echo 'Feed Logo:                 ' . $this->get_feed_logo() . PHP_EOL;
    echo 'Feed Generator:            ' . $this->get_feed_generator_name() . ' ' . $this->get_feed_generator_version() . PHP_EOL;
    echo 'Feed Updated:              ' . $this->get_feed_updated() . PHP_EOL;
    echo 'Feed Id:                   ' . $this->get_feed_id() . PHP_EOL;
  }

  private function close_atom_feed()
  {
    echo '</feed>';
  }

  private function close_rss_feed()
  {
    echo '  </channel>';
    echo '</rss>';
  }

  public function set_feed_format($value = null)
  {
    $this->feed_format = $value;
  }

  public function set_feed_generator_name($value = null)
  {
    $this->feed_generator_name = $value;
  }

  public function set_feed_generator_uri($value = null)
  {
    $this->feed_generator_uri = $value;
  }

  public function set_feed_generator_version($value = null)
  {
    $this->feed_generator_version = $value;
  }

  public function set_feed_icon($value = null)
  {
    $this->feed_icon = $value;
  }

  public function set_feed_id($value = null)
  {
    $this->feed_id = $value;
  }

  public function set_feed_link($value = null)
  {
    $this->feed_link = $value;
  }

  public function set_feed_link_alternate($value = null)
  {
    $this->feed_link_alternate = $value;
  }

  public function set_feed_logo($value = null)
  {
    $this->feed_logo = $value;
  }

  public function set_feed_title($value = null)
  {
    $this->feed_title = $value;
  }

  public function set_feed_updated($value = null)
  {
    $this->feed_updated = $value;
  }

  public function set_feed_website_link($value = null)
  {
    $this->feed_website_link = $value;
  }

  public function get_feed_format()
  {
    if ($this->feed_format !== null)
    {
      return $this->feed_format;
    } else {
      return null;
    }
  }

  public function get_feed_generator_name()
  {
    if ($this->feed_generator_name !== null)
    {
      return $this->feed_generator_name;
    } else {
      return null;
    }
  }

  public function get_feed_generator_uri()
  {
    if ($this->feed_generator_uri !== null)
    {
      return $this->feed_generator_uri;
    } else {
      return null;
    }
  }

  public function get_feed_generator_version()
  {
    if ($this->feed_generator_version !== null)
    {
      return $this->feed_generator_version;
    } else {
      return null;
    }
  }

  public function get_feed_icon()
  {
    if ($this->feed_icon !== null)
    {
      return $this->feed_icon;
    } else {
      return null;
    }
  }

  public function get_feed_id()
  {
    if ($this->feed_id !== null)
    {
      return $this->feed_id;
    } else {
      return null;
    }
  }

  public function get_feed_link()
  {
    if ($this->feed_link !== null)
    {
      return $this->feed_link;
    } else {
      return null;
    }
  }

  public function get_feed_link_alternate()
  {
    if ($this->feed_link_alternate !== null)
    {
      return $this->feed_link_alternate;
    } else {
      return null;
    }
  }

  public function get_feed_logo()
  {
    if ($this->feed_logo !== null)
    {
      return $this->feed_logo;
    } else {
      return null;
    }
  }

  public function get_feed_title()
  {
    if ($this->feed_title !== null)
    {
      return $this->feed_title;
    } else {
      return null;
    }
  }

  public function get_feed_updated()
  {
    if ($this->feed_updated !== null)
    {
      return $this->feed_updated;
    } else {
      return null;
    }
  }

  public function get_feed_website_link()
  {
    if ($this->feed_website_link !== null)
    {
      return $this->feed_website_link;
    } else {
      return null;
    }
  }
}
