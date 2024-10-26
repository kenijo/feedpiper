<?php

class Feed
{
    // Property declaration
    private $feedFormat = null;
    private $generatorName = null;
    private $generatorUri = null;
    private $generatorVersion = null;
    private $icon = null;
    private $id = null;
    private $link = null;
    private $linkAlternate = null;
    private $logo = null;
    private $title = null;
    private $updated = null;
    private $websiteLink = null;

    function __construct($feedFormat)
    {
        $this->setFormat($feedFormat);
    }

    public function open()
    {
        if ($this->feedFormat == 'ATOM') {
            $this->openAsAtom();
        } elseif ($this->feedFormat == 'RSS') {
            $this->openAsRss();
        }
    }

    public function close()
    {
        if ($this->feedFormat == 'ATOM') {
            $this->closeAsAtom();
        } elseif ($this->feedFormat == 'RSS') {
            $this->closeAsRss();
        }
    }

    private function openAsAtom()
    {
        // Atom Syndication Format
        // http://atomenabled.org/developers/syndication/
        echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
        echo '<feed xmlns:dc="http://purl.org/dc/elements/1.1/"' . PHP_EOL;
        echo '      xmlns:media="http://search.yahoo.com/mrss/"' . PHP_EOL;
        echo '      xmlns="http://www.w3.org/2005/Atom" >' . PHP_EOL;
        $this->debugHelp();

        // Contains a human readable title for the feed.
        // Often the same as the title of the associated website.
        // This value should not be blank.
        echo '  <title type="text">' . $this->getTitle() . '</title>' . PHP_EOL;

        // Contains the link to the original website providing the feed
        echo '  <link rel="related" type="text/html" title="' . $this->getTitle() . '" href="' . $this->getWebsiteLink() . '" />' . PHP_EOL;

        // RSS auto discovery is a technique that makes it possible for web browsers and other software to automatically
        // find a site's RSS feed. Auto discovery is a great way to inform users that a web site offers a syndication feed.
        // To support auto discovery, a link element must be added to the header, as shown in the HTML markup below.
        // Replace the href value of the link element with the URL of your RSS feed.
        echo '  <link rel="alternate" type="application/atom+xml" title="' . $this->getTitle() . '" href="' . $this->getLinkAlternate() . '" />' . PHP_EOL;

        // Identifies a related Web page.
        // The type of relation is defined by the rel attribute.
        // A feed is limited to one alternate per type and hreflang.
        // A feed should contain a link back to the feed itself.
        echo '  <link rel="self" type="application/atom+xml" title="' . $this->getTitle() . '" href="' . $this->getLink() . '" />' . PHP_EOL;

        // Identifies a small image which provides iconic visual identification for the feed.
        // Icons should be square.
        echo '  <icon>' . $this->getIcon() . '</icon>' . PHP_EOL;

        //Identifies a larger image which provides visual identification for the feed.
        // Images should be twice as wide as they are tall.
        echo '  <logo>' . $this->getLogo() . '</logo>' . PHP_EOL;

        // Identifies the software used to generate the feed, for debugging and other purposes.
        // Both the uri and version attributes are optional.
        echo '  <generator uri="' . $this->getGeneratorUri() . '" version="' . $this->getGeneratorVersion() . '">' . $this->getGeneratorName() . '</generator>' . PHP_EOL;

        // Indicates the last time the feed was modified in a significant way.
        // All timestamps in Atom must conform to RFC 3339.
        echo '  <updated>' . $this->getUpdated() . '</updated>' . PHP_EOL;

        // Identifies the feed using a universally unique and permanent URI.
        // If you have a long-term, renewable lease on your Internet domain name,
        // then you can feel free to use your website’s address.
        echo '  <id>' . $this->getLink() . '</id>' . PHP_EOL;

        // Separate entries from the header for clarity
        echo PHP_EOL;
    }

    private function openAsRss()
    {
        // RSS Syndication Format
        // http://atomenabled.org/developers/syndication/
        echo '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
        echo '<rss version="2.0"' . PHP_EOL;
        $this->debugHelp();
        echo '   xmlns:dc="http://purl.org/dc/elements/1.1/"' . PHP_EOL;
        echo '   xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">' . PHP_EOL;

        echo '  <channel>' . PHP_EOL;

        echo '    <title>' . $this->getTitle() . '</title>' . PHP_EOL;

        echo '    <link>' . $this->getWebsiteLink() . '</link>' . PHP_EOL;

        echo '    <description>' . $this->getTitle() . '</description>' . PHP_EOL;

        echo '    <pubDate>' . $this->getUpdated() . '</pubDate>' . PHP_EOL;
        echo '    <lastBuildDate>' . $this->getUpdated() . '</lastBuildDate>' . PHP_EOL;

        echo '    <image>' . PHP_EOL;
        echo '      <url>' . $this->getLogo() . '</url>' . PHP_EOL;
        echo '      <title>' . $this->getTitle() . '</title>' . PHP_EOL;
        echo '      <link>' . $this->getWebsiteLink() . '</link>' . PHP_EOL;
        echo '    </image>' . PHP_EOL;

        // Separate entries from the header for clarity
        echo PHP_EOL;
    }

    public function debug()
    {
        echo '########################################################################################################################' . PHP_EOL;
        echo 'Feed Title:                ' . $this->getTitle() . PHP_EOL;
        echo 'Feed Link:                 ' . $this->getLink() . PHP_EOL;
        echo 'Feed Link Alternative:     ' . $this->getLinkAlternate() . PHP_EOL;
        echo 'Feed Website Link:         ' . $this->getWebsiteLink() . PHP_EOL;
        echo 'Feed Icon:                 ' . $this->getIcon() . PHP_EOL;
        echo 'Feed Logo:                 ' . $this->getLogo() . PHP_EOL;
        echo 'Feed Generator:            ' . $this->getGeneratorName() . ' ' . $this->getGeneratorVersion() . PHP_EOL;
        echo 'Feed Updated:              ' . $this->getUpdated() . PHP_EOL;
        echo 'Feed Id:                   ' . $this->getId() . PHP_EOL;
    }

    private function debugHelp()
    {
        echo '  <!-- ######################################################################################################################## -->' . PHP_EOL;
        echo '  <!-- To enable debug mode, use the following link: -->' . PHP_EOL;
        echo '  <!--     ' . $this->getLink() . '?debug=true -->' . PHP_EOL;
        echo '  <!-- To debug a specific entry, specify an entry number: -->' . PHP_EOL;
        echo '  <!--     ' . $this->getLink() . '?debug=true&amp;entry=2 -->' . PHP_EOL;
        echo '  <!-- ######################################################################################################################## -->' . PHP_EOL;
    }

    private function closeAsAtom()
    {
        echo '</feed>';
    }

    private function closeAsRss()
    {
        echo '  </channel>';
        echo '</rss>';
    }

    public function setFormat($value = null)
    {
        $this->feedFormat = $value;
    }

    public function setGeneratorName($value = null)
    {
        $this->generatorName = $value;
    }

    public function setGeneratorUri($value = null)
    {
        $this->generatorUri = $value;
    }

    public function setGeneratorVersion($value = null)
    {
        $this->generatorVersion = $value;
    }

    public function setIcon($value = null)
    {
        $this->icon = $value;
    }

    public function setId($value = null)
    {
        $this->id = $value;
    }

    public function setLink($value = null)
    {
        $this->link = $value;
    }

    public function setLinkAlternate($value = null)
    {
        $this->linkAlternate = $value;
    }

    public function setLogo($value = null)
    {
        $this->logo = $value;
    }

    public function setTitle($value = null)
    {
        $this->title = $value;
    }

    public function setUpdated($value = null)
    {
        $this->updated = $value;
    }

    public function setWebsiteLink($value = null)
    {
        $this->websiteLink = $value;
    }

    public function getFormat()
    {
        if ($this->feedFormat !== null) {
            return $this->feedFormat;
        } else {
            return null;
        }
    }

    public function getGeneratorName()
    {
        if ($this->generatorName !== null) {
            return $this->generatorName;
        } else {
            return null;
        }
    }

    public function getGeneratorUri()
    {
        if ($this->generatorUri !== null) {
            return $this->generatorUri;
        } else {
            return null;
        }
    }

    public function getGeneratorVersion()
    {
        if ($this->generatorVersion !== null) {
            return $this->generatorVersion;
        } else {
            return null;
        }
    }

    public function getIcon()
    {
        if ($this->icon !== null) {
            return $this->icon;
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

    public function getLinkAlternate()
    {
        if ($this->linkAlternate !== null) {
            return $this->linkAlternate;
        } else {
            return null;
        }
    }

    public function getLogo()
    {
        if ($this->logo !== null) {
            return $this->logo;
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

    public function getWebsiteLink()
    {
        if ($this->websiteLink !== null) {
            return $this->websiteLink;
        } else {
            return null;
        }
    }
}
