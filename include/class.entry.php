<?php

class Entry
{
  // Property declaration
  private $entry_authors          = null;
  private $entry_categories       = null;
  private $entry_content          = null;
  private $entry_enclosure_length = null;
  private $entry_enclosure_link   = null;
  private $entry_enclosure_type   = null;
  private $entry_id               = null;
  private $entry_identifier       = null;
  private $entry_link             = null;
  private $entry_published        = null;
  private $entry_summary          = null;
  private $entry_title            = null;
  private $entry_updated          = null;
  private $feed_format            = null;

  function __construct($feed_format)
  {
    $this->set_feed_format($feed_format);
  }

  public function create_entry()
  {
    if ($this->feed_format == 'ATOM') {
      $this->create_atom_entry();
    } elseif ($this->feed_format == 'RSS') {
      $this->create_rss_entry();
    }
  }

  private function create_atom_entry()
  {
    // Create an entry
    echo '  <entry>' . PHP_EOL;

    // Contains a human readable title for the entry.
    // This value should not be blank.
    echo '    <title type="html">' . PHP_EOL;
    echo '      <![CDATA[' . PHP_EOL;
    echo '        ' . $this->get_entry_title() . PHP_EOL;
    echo '      ]]>' . PHP_EOL;
    echo '    </title>' . PHP_EOL;

    // Conveys a short summary, abstract, or excerpt of the entry.
    // Summary should be provided if there either is no content provided for the entry,
    // or that content is not inline (i.e., contains a src attribute), or if the content is encoded in base64.
    if ($this->get_entry_summary()) {
      echo '    <summary type="html">' . PHP_EOL;
      echo '      <![CDATA[' . PHP_EOL;
      echo '        ' . html_entity_decode($this->get_entry_summary());
      echo '      ]]>' . PHP_EOL;
      echo '    </summary>' . PHP_EOL;
    }

    // Contains or links to the complete content of the entry.
    // Content must be provided if there is no alternate link, and should be provided if there is no summary.
    if ($this->get_entry_content()) {
      echo '    <content type="html">' . PHP_EOL;
      echo '      <![CDATA[' . PHP_EOL;
      echo '        ' . html_entity_decode($this->get_entry_content());
      echo '     ]]>' . PHP_EOL;
      echo '    </content>' . PHP_EOL;
    } else if ($this->get_entry_link()) {
      echo '    <content type="application/xhtml+html" src="' . $this->get_entry_link() . '" />' . PHP_EOL;
    }

    // Add an enclosure entry
    if ($this->get_entry_enclosure_link()) {
      echo '    <link rel="enclosure" type="' . $this->get_entry_enclosure_type() . '" href="' . $this->get_entry_enclosure_link() . '" />' . PHP_EOL;
      echo '    <media:thumbnail url="' . $this->get_entry_enclosure_link() . '" />' . PHP_EOL;
    }

    // Identifies a related Web page. The type of relation is defined by the rel attribute.
    // An entry is limited to one alternate per type and hreflang.
    // An entry must contain an alternate link if there is no content element.
    if ($this->get_entry_link()) {
      echo '    <link rel="alternate" type="application/xhtml+html" href="' . $this->get_entry_link() . '" />' . PHP_EOL;
    }

    // Names one author of the entry. An entry may have multiple authors.
    // An entry must contain at least one author element unless there is an author element in the enclosing feed,
    // or there is an author element in the enclosed source element.
    // It has one required element, name, and two optional elements: uri, email.
    // <name> conveys a human-readable name for the person.
    // <uri> contains a home page for the person.
    // <email> contains an email address for the person.
    if ($this->get_entry_authors()) {
      foreach ($this->get_entry_authors() as $author) {
        echo '    <author><name>' . $author . '</name></author>'  . PHP_EOL;
      }
    }

    // Specifies a category that the entry belongs to. An entry may have multiple category elements.
    // A category has one required attribute, term, and two optional attributes, scheme and label.
    // <term> identifies the category
    // <scheme> identifies the categorization scheme via a URI.
    // <label> provides a human-readable label for display
    if ($this->get_entry_categories()) {
      foreach ($this->get_entry_categories() as $category) {
        echo '    <category term="' . $category . '" />'  . PHP_EOL;
      }
    }

    // Indicates the last time the entry was modified in a significant way.
    // This value need not change after a typo is fixed, only after a substantial modification.
    // Generally, different entries in a feed will have different updated timestamps.
    // All timestamps in Atom must conform to RFC 3339.
    echo '    <updated>' . $this->get_entry_updated() . '</updated>' . PHP_EOL;

    // Contains the time of the initial creation or first availability of the entry.
    // All timestamps in Atom must conform to RFC 3339.
    echo '    <published>' . $this->get_entry_published() . '</published>' . PHP_EOL;

    // Identifies the entry using a universally unique and permanent URI.
    // Two entries in a feed can have the same value for id if they represent the same entry at different points in time.
    if ($this->get_entry_link()) {
      echo '    <id>' . $this->get_entry_link() . '</id>' . PHP_EOL;
    }

    // Identifies the entry using an identifier unique to the entry
    //echo '    <dc:identifier>' . $this->get_entry_identifier() . '</dc:identifier>' . PHP_EOL;
    echo '    <dc:identifier>' . md5($this->get_entry_identifier()) . '</dc:identifier>' . PHP_EOL;

    // Close the entry
    echo '  </entry>' . PHP_EOL;

    // Separate entries from each others for clarity
    echo PHP_EOL;
  }

  private function create_rss_entry()
  {
    echo '  <item>' . PHP_EOL;

    echo '    <title><![CDATA[' . $this->get_entry_title() . ']]></title>' . PHP_EOL;

    if ($this->get_entry_summary()) {
      echo '    <description>' . PHP_EOL;
      echo '      <![CDATA[' . PHP_EOL;
      echo '        ' . html_entity_decode($this->get_entry_summary());
      echo '     ]]>' . PHP_EOL;
      echo '    </description>' . PHP_EOL;
    }

    if ($this->get_entry_enclosure_link()) {
      // If enclosure_type is null then make it a media:thumbnail
      if ($this->get_entry_enclosure_type() != null) {
        echo '    <enclosure url="' . $this->get_entry_enclosure_link() . '" length="' . $this->get_entry_enclosure_length() . '" type="' . $this->get_entry_enclosure_type() . '" />' . PHP_EOL;
      } else {
        echo '    <media:thumbnail url="' . $this->get_entry_enclosure_link() . '" />' . PHP_EOL;
      }
    }

    if ($this->get_entry_link()) {
      echo '    <link>' . $this->get_entry_link() . '</link>' . PHP_EOL;
    }

    if ($this->get_entry_authors()) {
      foreach ($this->get_entry_authors() as $author) {
        echo '    <dc:creator><![CDATA[' . $author . ']]></dc:creator>'  . PHP_EOL;
      }
    }

    if ($this->get_entry_categories()) {
      foreach ($this->get_entry_categories() as $category) {
        echo '    <category><![CDATA[' . $category . ']]></category>'  . PHP_EOL;
      }
    }

    echo '    <pubDate>' . $this->get_entry_published() . '</pubDate>' . PHP_EOL;

    if ($this->get_entry_link()) {
      echo '    <guid>' . $this->get_entry_link() . '</guid>' . PHP_EOL;
    } else {
      echo '    <guid>' . md5($this->get_entry_identifier()) . '</guid>' . PHP_EOL;
    }

    echo '    <dc:identifier>' . md5($this->get_entry_identifier()) . '</dc:identifier>' . PHP_EOL;

    echo '  </item>' . PHP_EOL;

    // Separate entries from each others for clarity
    echo PHP_EOL;
  }

  public function debug_entry()
  {
    echo '--------------------------------------------------------------------------------------------------------------' . PHP_EOL;
    echo 'Entry Title:               ' . '<![CDATA' . $this->get_entry_title() . ']]>' . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Summary:' . PHP_EOL;
    echo '  <![CDATA[' . PHP_EOL;
    echo '    ' . $this->get_entry_summary();
    echo ' ]]>' . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Content:' . PHP_EOL;
    echo '  <![CDATA[' . PHP_EOL;
    echo '    ' . $this->get_entry_content();
    echo ' ]]>' . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Link:                ' . $this->get_entry_link() . PHP_EOL;
    echo 'Entry Enclosure Link:      ' . $this->get_entry_enclosure_link() . PHP_EOL;
    echo 'Entry Enclosure Length:    ' . $this->get_entry_enclosure_length() . PHP_EOL;
    echo 'Entry Enclosure Type:      ' . $this->get_entry_enclosure_type() . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Author(s):           ' . get_array_as_string($this->get_entry_authors()) . PHP_EOL;
    echo 'Entry Categories:          ' . get_array_as_string($this->get_entry_categories()) . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Updated Date:        ' . $this->get_entry_updated() . PHP_EOL;
    echo 'Entry Published Date:      ' . $this->get_entry_published() . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Id:                  ' . $this->get_entry_id() . PHP_EOL;
    echo 'Entry Identifier:          ' . $this->get_entry_identifier() . PHP_EOL;
    echo 'Entry Identifier MD5:      ' . md5($this->get_entry_identifier()) . PHP_EOL;
  }

  public function set_entry_authors($value = null)
  {
    $this->entry_authors = $value;
  }

  public function set_entry_categories($value = null)
  {
    $this->entry_categories = $value;
  }

  public function set_entry_content($value = null)
  {
    $this->entry_content = $value;
  }

  public function set_entry_enclosure_length($value = null)
  {
    $this->entry_enclosure_length = $value;
  }

  public function set_entry_enclosure_link($value = null)
  {
    $this->entry_enclosure_link = $value;
  }

  public function set_entry_enclosure_type($value = null)
  {
    $this->entry_enclosure_type = $value;
  }

  public function set_entry_id($value = null)
  {
    $this->entry_id = $value;
  }

  public function set_entry_identifier($value = null)
  {
    $this->entry_identifier = $value;
  }

  public function set_entry_link($value = null)
  {
    $this->entry_link = $value;
  }

  public function set_entry_published($value = null)
  {
    $this->entry_published = $value;
  }

  public function set_entry_summary($value = null)
  {
    $this->entry_summary = $value;
  }

  public function set_entry_title($value = null)
  {
    $this->entry_title = $value;
  }

  public function set_entry_updated($value = null)
  {
    $this->entry_updated = $value;
  }

  public function set_feed_format($value = null)
  {
    $this->feed_format = $value;
  }

  public function get_entry_authors()
  {
    if ($this->entry_authors !== null) {
      return $this->entry_authors;
    } else {
      return null;
    }
  }

  public function get_entry_categories()
  {
    if ($this->entry_categories !== null) {
      return $this->entry_categories;
    } else {
      return null;
    }
  }

  public function get_entry_content()
  {
    if ($this->entry_content !== null) {
      return $this->entry_content;
    } else {
      return null;
    }
  }

  public function get_entry_enclosure_length()
  {
    if ($this->entry_enclosure_length !== null) {
      return $this->entry_enclosure_length;
    } else {
      return null;
    }
  }

  public function get_entry_enclosure_link()
  {
    if ($this->entry_enclosure_link !== null) {
      return $this->entry_enclosure_link;
    } else {
      return null;
    }
  }

  public function get_entry_enclosure_type()
  {
    if ($this->entry_enclosure_type !== null) {
      return $this->entry_enclosure_type;
    } else {
      return null;
    }
  }

  public function get_entry_id()
  {
    if ($this->entry_id !== null) {
      return $this->entry_id;
    } else {
      return null;
    }
  }

  public function get_entry_identifier()
  {
    if ($this->entry_identifier !== null) {
      return $this->entry_identifier;
    } else {
      return null;
    }
  }

  public function get_entry_link()
  {
    if ($this->entry_link !== null) {
      return $this->entry_link;
    } else {
      return null;
    }
  }

  public function get_entry_published()
  {
    if ($this->entry_published !== null) {
      return $this->entry_published;
    } else {
      return null;
    }
  }

  public function get_entry_summary()
  {
    if ($this->entry_summary !== null) {
      return $this->entry_summary;
    } else {
      return null;
    }
  }

  public function get_entry_title()
  {
    if ($this->entry_title !== null) {
      return $this->entry_title;
    } else {
      return null;
    }
  }

  public function get_entry_updated()
  {
    if ($this->entry_updated !== null) {
      return $this->entry_updated;
    } else {
      return null;
    }
  }

  public function get_feed_format()
  {
    if ($this->feed_format !== null) {
      return $this->feed_format;
    } else {
      return null;
    }
  }
}
