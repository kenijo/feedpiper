<?php

/**
 * FeedFilter Class
 *
 * This class is designed to filter and process RSS feeds using the SimplePie library.
 * It provides methods to open, add entries to, and close RSS feeds, as well as filter
 * entries based on whitelist and blacklist criteria.
 */
class FeedFilter
{
    private const RSS_VERSION = '2.0';
    private const ATOM_NAMESPACE = 'http://www.w3.org/2005/Atom';
    private const CONTENT_NAMESPACE = 'http://purl.org/rss/1.0/modules/content/';
    private const DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';

    private $feed = null;
    private $feedBlacklist = null;
    private $feedDescription = null;
    private $feedGenerator = null;
    private $feedLanguage = null;
    private $feedLastBuildDate = null;
    private $feedLink = null;
    private $feedTitle = null;
    private $feedWhitelist = null;

    private $feedEntryAuthors = null;
    private $feedEntryCategories = null;
    private $feedEntryContent = null;
    private $feedEntryDescription = null;
    private $feedEntryEnclosure = null;
    private $feedEntryId = null;
    private $feedEntryLink = null;
    private $feedEntryPubDate = null;
    private $feedEntrySkip = false;
    private $feedEntryTitle = null;

    /**
     * Constructor
     *
     * Initializes the FeedFilter object with a SimplePie feed.
     *
     * @param SimplePie $simplePieFeed The SimplePie feed object.
     */
    public function __construct(SimplePie $simplePieFeed)
    {
        $this->setFeed($simplePieFeed);

        $this->setFeedTitle($simplePieFeed->get_title());
        $this->setFeedDescription($simplePieFeed->get_description());
        $this->setFeedLink($simplePieFeed->get_link());

        $this->setFeedLanguage($simplePieFeed->get_language());
        $this->setFeedLastBuildDate();
        $this->setFeedGenerator();
    }

    /**
     * Opens the RSS feed and prints the opening tags.
     */
    public function openFeed(): void
    {
        $this->debugHelp();

        // RSS Syndication Format
        // https://www.rssboard.org/rss-profile#element-channel
        echo '<rss version="' . self::RSS_VERSION . '" xmlns:atom="' . self::ATOM_NAMESPACE . '" xmlns:content="' . self::CONTENT_NAMESPACE . '" xmlns:dc="' . self::DC_NAMESPACE . '">' . PHP_EOL;
        $this->feedEntryPrint(1, 'channel', '');
        $this->feedEntryPrint(2, 'title', $this->getFeedTitle(), true);
        $this->feedEntryPrint(2, 'description', $this->getFeedDescription(), true);
        $this->feedEntryPrint(2, 'link', $this->getFeedLink());
        $this->feedEntryPrint(2, 'language', $this->getFeedLanguage());
        $this->feedEntryPrint(2, 'lastBuildDate', $this->getFeedLastBuildDate());
        $this->feedEntryPrint(2, 'generator', $this->getFeedGenerator(), true);
    }

    /**
     * Adds an entry to the RSS feed.
     */
    public function addFeedEntry(): void
    {
        if ($authors = $this->getFeedEntryAuthors()) {
            $authors = 'Authors: ' . implode(', ', $authors);
        }
        if ($categories = $this->getFeedEntryCategories()) {
            $categories = 'Categories: ' . implode(', ', $categories);
        }

        // RSS Syndication Format
        // https://www.rssboard.org/rss-profile#element-channel-item
        $this->feedEntryPrint(2, 'item', '');
        $this->feedEntryPrint(3, 'title', $this->getFeedEntryTitle(), true);
        if ($description = $this->getFeedEntryDescription()) {
            $description .= '<br />';
            if ($authors) {
                $description .= '<br />' . PHP_EOL . $authors;
            }
            if ($categories) {
                $description .= '<br />' . PHP_EOL . $categories;
            }
            $this->feedEntryPrint(3, 'description', $description, true);
        }
        if ($content = $this->getFeedEntryContent()) {
            if ($authors) {
                $content .= '<br />';
                $content .= '<br />' . PHP_EOL . $authors;
            }
            if ($categories) {
                $content .= '<br />' . PHP_EOL . $categories;
            }
            $this->feedEntryPrint(3, 'content:encoded', $content, true);
        }
        $this->feedEntryPrint(3, 'link', $this->getFeedEntryLink());
        $this->feedEntryPrint(3, 'guid', $this->getFeedEntryId());
        $this->feedEntryPrint(3, 'pubDate', $this->getFeedEntryPubDate());
        foreach ($this->getFeedEntryAuthors() as $author) {
            $this->feedEntryPrint(3, 'dc:creator', $author, true);
        }
        foreach ($this->getFeedEntryCategories() as $category) {
            $this->feedEntryPrint(3, 'category', $category, true);
        }
        if ($this->getFeedEntryEnclosure()) {
            $this->feedEntryPrint(3, 'enclosure', $this->getFeedEntryEnclosure());
        }
        $this->feedEntryPrint(2, 'item', '');
    }

    /**
     * Prints an RSS feed entry tag.
     *
     * @param int $indentLevel The level of indentation.
     * @param string $tag The tag name.
     * @param string|null $value The tag value.
     * @param bool $cdata Whether to wrap the value in a CDATA section.
     */
    private function feedEntryPrint($indentLevel, $tag, $value = null, $cdata = false)
    {
        if ($value === '') {
            // Determine if it is an opening or closing tag
            static $feedEntryTags = [];
            if (in_array($tag, $feedEntryTags)) {
                // It's a closing tag
                array_pop($feedEntryTags);
                echo str_repeat('  ', $indentLevel) . "</$tag>" . PHP_EOL;
            } else {
                // It's an opening tag
                $feedEntryTags[] = $tag;
                echo str_repeat('  ', $indentLevel) . "<$tag>" . PHP_EOL;
            }
        } elseif ($value !== null) {
            if ($cdata) {
                $value = '<![CDATA[ ' . html_entity_decode($value) . ' ]]>';
            }
            // Print tag with value
            if ($tag == 'enclosure') {
                // Print tag enclosure
                echo str_repeat('  ', $indentLevel) . "<$tag";
                if ($this->getFeedEntryEnclosure()->get_length() != null || $this->getFeedEntryEnclosure()->get_length() != '') {
                    echo ' length="' . $this->getFeedEntryEnclosure()->get_length() . '"';
                }
                if ($this->getFeedEntryEnclosure()->get_link() != null || $this->getFeedEntryEnclosure()->get_link() != '') {
                    echo ' url="' . clean_query_params($this->getFeedEntryEnclosure()->get_link()) . '"';
                }

                if ($this->getFeedEntryEnclosure()->get_type() != null || $this->getFeedEntryEnclosure()->get_type() != '') {
                    echo ' type="' . $this->getFeedEntryEnclosure()->get_type() . '"';
                }
                echo '/>' . PHP_EOL;
            } else {
                // Print tag with value
                echo str_repeat('  ', $indentLevel) . "<$tag>$value</$tag>" . PHP_EOL;
            }
        }
    }

    /**
     * Closes the RSS feed and prints the closing tags.
     */
    public function closeFeed()
    {
        $this->feedEntryPrint(1, 'channel', '');
        echo '</rss>' . PHP_EOL;
    }

    /**
     * Prints debug information about the feed.
     */
    public function debugFeed()
    {
        $this->debugPrint('Feed Title:', $this->getFeedTitle());
        $this->debugPrint('Feed Description:', $this->getFeedDescription());
        $this->debugPrint('Feed Link:', $this->getFeedLink());
        $this->debugPrint('Feed Language:', $this->getFeedLanguage());
        $this->debugPrint('Feed Last Build Date:', $this->getFeedLastBuildDate());
        $this->debugPrint('Feed Generator:', $this->getFeedGenerator());
        echo PHP_EOL;
    }

    /**
     * Prints debug information about a feed entry.
     */
    public function debugFeedEntry()
    {
        $this->debugPrint('Entry Title:', $this->getFeedEntryTitle());
        if ($this->getFeedEntryDescription()) {
            echo PHP_EOL;
            $this->debugPrint('Entry Description:', $this->getFeedEntryDescription());
        }
        if ($this->getFeedEntryContent()) {
            echo PHP_EOL;
            $this->debugPrint('Entry Content:', $this->getFeedEntryContent());
        }
        echo PHP_EOL;
        $this->debugPrint('Entry Link:', $this->getFeedEntryLink());
        $this->debugPrint('Entry GUID:', $this->getFeedEntryId());
        $this->debugPrint('Entry Pub Date:', $this->getFeedEntryPubDate());
        if ($this->getFeedEntryAuthors()) {
            $this->debugPrint('Entry Authors:', implode(', ', $this->getFeedEntryAuthors()));
        }
        if ($this->getFeedEntryCategories()) {
            $this->debugPrint('Entry Categories:', implode(', ', $this->getFeedEntryCategories()));
        }
        echo PHP_EOL;
        if ($this->getFeedEntryEnclosure()) {
            $this->debugPrint('Entry Enclosure Length:', $this->getFeedEntryEnclosure()->get_length());
            $this->debugPrint('Entry Enclosure Link:', clean_query_params($this->getFeedEntryEnclosure()->get_link()));
            $this->debugPrint('Entry Enclosure Type:', $this->getFeedEntryEnclosure()->get_type());
        }
        if ($this->getFeedWhitelist() !== null) {
            foreach ($this->getFeedWhitelist() as $type => $conditions) {
                foreach ($conditions as $condition => $value) {
                    echo PHP_EOL;
                    $this->debugPrint('Whitelist ' . ucfirst($type) . ' ' . strtoupper($condition) . ':', implode(', ', $value));
                }
            }
        }
        if ($this->getFeedBlacklist() !== null) {
            foreach ($this->getFeedBlacklist() as $type => $conditions) {
                foreach ($conditions as $condition => $value) {
                    echo PHP_EOL;
                    $this->debugPrint('Blacklist ' . ucfirst($type) . ' ' . strtoupper($condition) . ':', implode(', ', $value));
                }
            }
        }
        echo PHP_EOL;
        $this->debugPrint('SKIP ENTRY:', $this->getFeedEntrySkip() ? 'TRUE' : 'FALSE');
    }

    /**
     * Prints a debug message.
     *
     * @param string $element The element name.
     * @param string $value The element value.
     */
    public function debugPrint($element, $value)
    {
        $length = 35;
        echo $element . str_repeat(' ', $length - strlen($element)) . $value . PHP_EOL;
    }

    /**
     * Prints debug help information.
     */
    private function debugHelp()
    {
        echo '<!--' . PHP_EOL;
        echo '  ########################################################################################################################' . PHP_EOL;
        echo '    To enable debug mode, use the following link:                                                                           ' . PHP_EOL;
        echo '      https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&debug=true                                            ' . PHP_EOL;
        echo '    To debug a specific entry, specify an entry number:                                                                     ' . PHP_EOL;
        echo '      https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&debug=true&entry=2                                    ' . PHP_EOL;
        echo '  ########################################################################################################################' . PHP_EOL;
        echo '-->' . PHP_EOL;
    }

    /**
     * Filters feed entries based on whitelist and blacklist criteria.
     * Never skip an entry if it matches a whitelist filter (takes precedence over blacklist)
     * Always skip an entry if it matches a blacklist filter.
     *
     * @param array $entries The feed entries to filter.
     */
    public function filterEntries($entries)
    {
        // Check each entry against the whitelist and blacklist filter
        foreach ($entries as $entryType => $entryValues) {
            if (is_string($entryValues)) {
                $entryValues = [$entryValues];
            }
            foreach ($entryValues as $entryValue) {
                $keep = $this->checkEntryAgainstList($entryValue, $entryType, $this->getFeedWhitelist());
                if ($keep) {
                    $this->setFeedEntrySkip(false);
                    return; // Stop further processing if a match is found in the whitelist
                }
                $skip = $this->checkEntryAgainstList($entryValue, $entryType, $this->getFeedBlacklist());
                if ($skip) {
                    $this->setFeedEntrySkip(true);
                    return; // Stop further processing if a match is found in the blacklist
                }
            }
        }
        $this->setFeedEntrySkip(false);
    }

    /**
     * Checks an entry value against a list of filters.
     *
     * @param string $entryValue The value of the entry to check.
     * @param string $entryType The type of the entry (e.g., 'title', 'content').
     * @param array $list The list of filters to check against.
     * @return bool True if the entry value matches any filter in the list, false otherwise.
     */
    private function checkEntryAgainstList($entryValue, $entryType, $list)
    {
        if ($entryType == 'content' && $entryValue == '') {
            return true; // Skip entry if it has empty descriptions or content
        }

        $entryValue = html_entity_decode($entryValue);  // Decode HTML entities
        $entryValue = strip_tags($entryValue);          // Remove all HTML tags
        $entryValue = removeAccents($entryValue);         // Remove accents from characters
        $entryValue = strtolower($entryValue);          // Convert to lowercase

        if (isset($list[$entryType])) {
            foreach (['starts', 'contains', 'ends', 'regex'] as $condition) {
                if (isset($list[$entryType][$condition])) {
                    foreach ($list[$entryType][$condition] as $value) {
                        if (
                            ($condition === 'starts' && preg_match('#^\b' . preg_quote($value, '#') . '\b#imu', $entryValue)) ||
                            ($condition === 'contains' && preg_match('#\b' . preg_quote($value, '#') . '\b#imu', $entryValue)) ||
                            ($condition === 'ends' && preg_match('#\b' . preg_quote($value, '#') . '\b$#imu', $entryValue)) ||
                            ($condition === 'regex' && preg_match('#' . $value . '#imu', $entryValue))
                        ) {
                            return true; // Skip entry if a match is found
                        }
                    }
                }
            }
        }
        return false;
    }

    public function getFeed()
    {
        return $this->feed;
    }
    public function getFeedBlacklist()
    {
        return $this->feedBlacklist;
    }
    public function getFeedDescription()
    {
        return $this->feedDescription;
    }
    public function getFeedGenerator()
    {
        return $this->feedGenerator;
    }
    public function getFeedLanguage()
    {
        return $this->feedLanguage;
    }
    public function getFeedLastBuildDate()
    {
        return $this->feedLastBuildDate;
    }
    public function getFeedLink()
    {
        return $this->feedLink;
    }
    public function getFeedTitle()
    {
        return $this->feedTitle;
    }
    public function getFeedWhitelist()
    {
        return $this->feedWhitelist;
    }

    public function getFeedEntryAuthors()
    {
        return $this->feedEntryAuthors;
    }
    public function getFeedEntryCategories()
    {
        return $this->feedEntryCategories;
    }
    public function getFeedEntryContent()
    {
        return $this->feedEntryContent;
    }
    public function getFeedEntryDescription()
    {
        return $this->feedEntryDescription;
    }
    public function getFeedEntryEnclosure()
    {
        return $this->feedEntryEnclosure;
    }
    public function getFeedEntryId()
    {
        return $this->feedEntryId;
    }
    public function getFeedEntryLink()
    {
        return $this->feedEntryLink;
    }
    public function getFeedEntryPubDate()
    {
        return $this->feedEntryPubDate;
    }
    public function getFeedEntrySkip()
    {
        return $this->feedEntrySkip;
    }
    public function getFeedEntryTitle()
    {
        return $this->feedEntryTitle;
    }

    public function setFeed($feed)
    {
        $this->feed = $feed;
    }
    public function setFeedBlacklist($feedBlacklist)
    {
        $this->feedBlacklist = $feedBlacklist;
    }
    public function setFeedDescription($feedDescription)
    {
        $this->feedDescription = $feedDescription;
    }
    public function setFeedGenerator()
    {
        $this->feedGenerator = 'FeedPiper using ' . SimplePie::NAME . ' ' . SimplePie::VERSION;
    }
    public function setFeedLanguage($feedLanguage = null)
    {
        $this->feedLanguage = $feedLanguage;
    }
    public function setFeedLastBuildDate()
    {
        $this->feedLastBuildDate = (new DateTime('now', new DateTimezone('GMT')))->format(DATE_RSS);
    }
    public function setFeedLink($feedLink)
    {
        $this->feedLink = $feedLink;
    }
    public function setFeedTitle($feedTitle)
    {
        $this->feedTitle = $feedTitle;
    }
    public function setFeedWhitelist($feedWhitelist)
    {
        $this->feedWhitelist = $feedWhitelist;
    }

    public function setFeedEntryAuthors($feedEntryAuthors)
    {
        $this->feedEntryAuthors = $feedEntryAuthors;
    }
    public function setFeedEntryCategories($feedEntryCategories)
    {
        $this->feedEntryCategories = $feedEntryCategories;
    }
    public function setFeedEntryContent($feedEntryContent)
    {
        $this->feedEntryContent = $feedEntryContent;
    }
    public function setFeedEntryDescription($feedEntryDescription)
    {
        $this->feedEntryDescription = $feedEntryDescription;
    }
    public function setFeedEntryEnclosure($feedEntryEnclosure)
    {
        $this->feedEntryEnclosure = $feedEntryEnclosure;
    }
    public function setFeedEntryId($feedEntryId)
    {
        $this->feedEntryId = $feedEntryId;
    }
    public function setFeedEntryLink($feedEntryLink)
    {
        $this->feedEntryLink = $feedEntryLink;
    }
    public function setFeedEntryPubDate($feedEntryPubDate)
    {
        $this->feedEntryPubDate = $feedEntryPubDate;
    }
    public function setFeedEntrySkip($feedEntrySkip)
    {
        $this->feedEntrySkip = $feedEntrySkip;
    }
    public function setFeedEntryTitle($feedEntryTitle)
    {
        $this->feedEntryTitle = $feedEntryTitle;
    }
}
