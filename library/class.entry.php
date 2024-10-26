<?php

class Entry
{
  // Property declaration
  private $authors          = null;
  private $categories       = null;
  private $content          = null;
  private $enclosureLength = null;
  private $enclosureLink   = null;
  private $enclosureType   = null;
  private $id               = null;
  private $identifier       = null;
  private $link             = null;
  private $published        = null;
  private $summary          = null;
  private $title            = null;
  private $updated          = null;
  private $feedFormat            = null;

  function __construct($feedFormat)
  {
    $this->setFeedFormat($feedFormat);
  }

  public function add()
  {
    if ($this->feedFormat == 'ATOM') {
      $this->addAsAtom();
    } elseif ($this->feedFormat == 'RSS') {
      $this->addAsRss();
    }
  }

  private function addAsAtom()
  {
    // Create an entry
    echo '  <entry>' . PHP_EOL;

    // Contains a human readable title for the entry.
    // This value should not be blank.
    echo '    <title type="html">' . PHP_EOL;
    echo '      <![CDATA[' . PHP_EOL;
    echo '        ' . $this->getTitle() . PHP_EOL;
    echo '      ]]>' . PHP_EOL;
    echo '    </title>' . PHP_EOL;

    // Conveys a short summary, abstract, or excerpt of the entry.
    // Summary should be provided if there either is no content provided for the entry,
    // or that content is not inline (i.e., contains a src attribute), or if the content is encoded in base64.
    if ($this->getSummary()) {
      echo '    <summary type="html">' . PHP_EOL;
      echo '      <![CDATA[' . PHP_EOL;
      echo '        ' . html_entity_decode($this->getSummary());
      echo '      ]]>' . PHP_EOL;
      echo '    </summary>' . PHP_EOL;
    }

    // Contains or links to the complete content of the entry.
    // Content must be provided if there is no alternate link, and should be provided if there is no summary.
    if ($this->getContent()) {
      echo '    <content type="html">' . PHP_EOL;
      echo '      <![CDATA[' . PHP_EOL;
      echo '        ' . html_entity_decode($this->getContent());
      echo '     ]]>' . PHP_EOL;
      echo '    </content>' . PHP_EOL;
    } else if ($this->getLink()) {
      echo '    <content type="application/xhtml+html" src="' . $this->getLink() . '" />' . PHP_EOL;
    }

    // Add an enclosure entry
    if ($this->getEnclosureLink()) {
      echo '    <link rel="enclosure" type="' . $this->getEnclosureType() . '" href="' . $this->getEnclosureLink() . '" />' . PHP_EOL;
      echo '    <media:thumbnail url="' . $this->getEnclosureLink() . '" />' . PHP_EOL;
    }

    // Identifies a related Web page. The type of relation is defined by the rel attribute.
    // An entry is limited to one alternate per type and hreflang.
    // An entry must contain an alternate link if there is no content element.
    if ($this->getLink()) {
      echo '    <link rel="alternate" type="application/xhtml+html" href="' . $this->getLink() . '" />' . PHP_EOL;
    }

    // Names one author of the entry. An entry may have multiple authors.
    // An entry must contain at least one author element unless there is an author element in the enclosing feed,
    // or there is an author element in the enclosed source element.
    // It has one required element, name, and two optional elements: uri, email.
    // <name> conveys a human-readable name for the person.
    // <uri> contains a home page for the person.
    // <email> contains an email address for the person.
    if ($this->getAuthors()) {
      foreach ($this->getAuthors() as $author) {
        echo '    <author><name>' . $author . '</name></author>'  . PHP_EOL;
      }
    }

    // Specifies a category that the entry belongs to. An entry may have multiple category elements.
    // A category has one required attribute, term, and two optional attributes, scheme and label.
    // <term> identifies the category
    // <scheme> identifies the categorization scheme via a URI.
    // <label> provides a human-readable label for display
    if ($this->getCategories()) {
      foreach ($this->getCategories() as $category) {
        echo '    <category term="' . $category . '" />'  . PHP_EOL;
      }
    }

    // Indicates the last time the entry was modified in a significant way.
    // This value need not change after a typo is fixed, only after a substantial modification.
    // Generally, different entries in a feed will have different updated timestamps.
    // All timestamps in Atom must conform to RFC 3339.
    echo '    <updated>' . $this->getUpdated() . '</updated>' . PHP_EOL;

    // Contains the time of the initial creation or first availability of the entry.
    // All timestamps in Atom must conform to RFC 3339.
    echo '    <published>' . $this->getPublished() . '</published>' . PHP_EOL;

    // Identifies the entry using a universally unique and permanent URI.
    // Two entries in a feed can have the same value for id if they represent the same entry at different points in time.
    if ($this->getLink()) {
      echo '    <id>' . $this->getLink() . '</id>' . PHP_EOL;
    }

    // Identifies the entry using an identifier unique to the entry
    //echo '    <dc:identifier>' . $this->get_entry_identifier() . '</dc:identifier>' . PHP_EOL;
    echo '    <dc:identifier>' . md5($this->getIdentifier()) . '</dc:identifier>' . PHP_EOL;

    // Close the entry
    echo '  </entry>' . PHP_EOL;

    // Separate entries from each others for clarity
    echo PHP_EOL;
  }

  private function addAsRss()
  {
    echo '  <item>' . PHP_EOL;

    echo '    <title><![CDATA[' . $this->getTitle() . ']]></title>' . PHP_EOL;

    if ($this->getSummary()) {
      echo '    <description>' . PHP_EOL;
      echo '      <![CDATA[' . PHP_EOL;
      echo '        ' . html_entity_decode($this->getSummary());
      echo '     ]]>' . PHP_EOL;
      echo '    </description>' . PHP_EOL;
    }

    if ($this->getEnclosureLink()) {
      // If enclosure_type is null then make it a media:thumbnail
      if ($this->getEnclosureType() != null) {
        echo '    <enclosure url="' . $this->getEnclosureLink() . '" length="' . $this->getEnclosureLength() . '" type="' . $this->getEnclosureType() . '" />' . PHP_EOL;
      } else {
        echo '    <media:thumbnail url="' . $this->getEnclosureLink() . '" />' . PHP_EOL;
      }
    }

    if ($this->getLink()) {
      echo '    <link>' . $this->getLink() . '</link>' . PHP_EOL;
    }

    if ($this->getAuthors()) {
      foreach ($this->getAuthors() as $author) {
        echo '    <dc:creator><![CDATA[' . $author . ']]></dc:creator>'  . PHP_EOL;
      }
    }

    if ($this->getCategories()) {
      foreach ($this->getCategories() as $category) {
        echo '    <category><![CDATA[' . $category . ']]></category>'  . PHP_EOL;
      }
    }

    echo '    <pubDate>' . $this->getPublished() . '</pubDate>' . PHP_EOL;

    if ($this->getLink()) {
      echo '    <guid>' . $this->getLink() . '</guid>' . PHP_EOL;
    } else {
      echo '    <guid>' . md5($this->getIdentifier()) . '</guid>' . PHP_EOL;
    }

    echo '    <dc:identifier>' . md5($this->getIdentifier()) . '</dc:identifier>' . PHP_EOL;

    echo '  </item>' . PHP_EOL;

    // Separate entries from each others for clarity
    echo PHP_EOL;
  }

  public function debug()
  {
    echo '--------------------------------------------------------------------------------------------------------------' . PHP_EOL;
    echo 'Entry Title:               ' . '<![CDATA' . $this->getTitle() . ']]>' . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Summary:' . PHP_EOL;
    echo '  <![CDATA[' . PHP_EOL;
    echo '    ' . $this->getSummary();
    echo ' ]]>' . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Content:' . PHP_EOL;
    echo '  <![CDATA[' . PHP_EOL;
    echo '    ' . $this->getContent();
    echo ' ]]>' . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Link:                ' . $this->getLink() . PHP_EOL;
    echo 'Entry Enclosure Link:      ' . $this->getEnclosureLink() . PHP_EOL;
    echo 'Entry Enclosure Length:    ' . $this->getEnclosureLength() . PHP_EOL;
    echo 'Entry Enclosure Type:      ' . $this->getEnclosureType() . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Author(s):           ' . convertArrayToCommaSeparatedString($this->getAuthors()) . PHP_EOL;
    echo 'Entry Categories:          ' . convertArrayToCommaSeparatedString($this->getCategories()) . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Updated Date:        ' . $this->getUpdated() . PHP_EOL;
    echo 'Entry Published Date:      ' . $this->getPublished() . PHP_EOL;
    echo PHP_EOL;
    echo 'Entry Id:                  ' . $this->getId() . PHP_EOL;
    echo 'Entry Identifier:          ' . $this->getIdentifier() . PHP_EOL;
    echo 'Entry Identifier MD5:      ' . md5($this->getIdentifier()) . PHP_EOL;
  }

  public function setAuthors($value = null)
  {
    $this->authors = $value;
  }

  public function setCategories($value = null)
  {
    $this->categories = $value;
  }

  public function setContent($value = null)
  {
    $this->content = $value;
  }

  public function setEnclosureLength($value = null)
  {
    $this->enclosureLength = $value;
  }

  public function setEnclosureLink($value = null)
  {
    $this->enclosureLink = $value;
  }

  public function setEnclosureType($value = null)
  {
    $this->enclosureType = $value;
  }

  public function setId($value = null)
  {
    $this->id = $value;
  }

  public function setIdentifier($value = null)
  {
    $this->identifier = $value;
  }

  public function setLink($value = null)
  {
    $this->link = $value;
  }

  public function setPublished($value = null)
  {
    $this->published = $value;
  }

  public function setSummary($value = null)
  {
    $this->summary = $value;
  }

  public function setTitle($value = null)
  {
    $this->title = $value;
  }

  public function setUpdated($value = null)
  {
    $this->updated = $value;
  }

  public function setFeedFormat($value = null)
  {
    $this->feedFormat = $value;
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

  public function getId()
  {
    if ($this->id !== null) {
      return $this->id;
    } else {
      return null;
    }
  }

  public function getIdentifier()
  {
    if ($this->identifier !== null) {
      return $this->identifier;
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

  public function getPublished()
  {
    if ($this->published !== null) {
      return $this->published;
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

  public function getUpdated()
  {
    if ($this->updated !== null) {
      return $this->updated;
    } else {
      return null;
    }
  }

  public function getFeedFormat()
  {
    if ($this->feedFormat !== null) {
      return $this->feedFormat;
    } else {
      return null;
    }
  }
}
