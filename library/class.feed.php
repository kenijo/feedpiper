<?php

/**
 * Feed Class
 *
 * This class handles the creation and management of RSS feeds for the FeedPiper application.
 * It provides methods to set feed properties and generate properly formatted RSS 2.0 output.
 *
 * The Feed class is responsible for:
 * - Creating the RSS feed structure with proper XML formatting
 * - Managing feed metadata (title, description, links, etc.)
 * - Providing both normal and debug output modes
 * - Supporting standard RSS 2.0 specifications with additional namespaces
 */
class Feed
{
    // Constants for RSS specifications and formatting
    private const RSS_VERSION = '2.0';                                              // RSS version used in output
    private const ATOM_NAMESPACE = 'http://www.w3.org/2005/Atom';                   // Atom namespace for extended compatibility
    private const CONTENT_NAMESPACE = 'http://purl.org/rss/1.0/modules/content/';   // Content namespace for rich content
    private const DC_NAMESPACE = 'http://purl.org/dc/elements/1.1/';                // Dublin Core namespace for metadata
    private const DEBUG_PADDING_LENGTH = 35;                                            // Character padding for debug output formatting

    // Feed properties (core RSS channel elements)
    private ?string $cache_location = null; // Feed cache location
    private ?string $description = null;    // Feed description - explains the feed's purpose
    private string $generator = 'FeedPiper using ' . SimplePie::NAME . ' ' . SimplePie::VERSION;    // Generator information - identifies the software creating the feed
    private ?string $language = null;       // Feed language - identifies the language the feed is written in (e.g., 'en-us')
    private ?string $lastBuildDate = null;  // Last build date in RSS format - indicates when the feed was last updated
    private ?array $link = null;            // Feed links - URLs to the HTML website corresponding to the feed
    private ?string $title = null;          // Feed title - name of the feed, typically matching the associated website

    /**
     * Constructor
     *
     * Initializes a new Feed object with the current date as the lastBuildDate.
     * Uses GMT timezone to ensure consistency across different server configurations.
     */
    public function __construct()
    {
        $this->lastBuildDate = (new DateTime('now', new DateTimeZone('GMT')))->format(DATE_RSS);
    }

    /**
     * Prints the opening XML elements of the RSS feed
     *
     * Includes debug information as a comment and the channel opening tags with feed metadata.
     * This method outputs the RSS header and channel elements according to RSS 2.0 specification.
     * It should be called before any feed entries are printed.
     *
     * @return void
     */
    public function printOpen(): void
    {
        // Add helpful debug instructions as XML comments
        echo '<!--' . PHP_EOL;
        echo '  ########################################################################################################################' . PHP_EOL;
        echo '    To enable debug mode, use the following link:                                                                           ' . PHP_EOL;
        echo '      https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&debug=true                                            ' . PHP_EOL;
        echo '    To debug a specific entry, specify an entry number:                                                                     ' . PHP_EOL;
        echo '      https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&debug=true&entry=2                                    ' . PHP_EOL;
        echo '  ########################################################################################################################' . PHP_EOL;
        echo '-->' . PHP_EOL;

        // Output RSS opening tag with namespaces for extended functionality
        $this->printFormat(0, 'rss version="' . self::RSS_VERSION . '" xmlns:atom="' . self::ATOM_NAMESPACE . '" xmlns:content="' . self::CONTENT_NAMESPACE . '" xmlns:dc="' . self::DC_NAMESPACE . '"');
        $this->printFormat(1, 'channel');

        // Output required channel elements according to RSS spec
        $this->printFormat(2, 'title', $this->getTitle(), true);
        $this->printFormat(2, 'description', $this->getDescription(), true);
        $this->printFormat(2, 'link', $this->getLink());

        // Output optional but recommended channel elements
        $this->printFormat(2, 'language', $this->getLanguage());
        $this->printFormat(2, 'lastBuildDate', $this->getLastBuildDate());
        $this->printFormat(2, 'generator', $this->getGenerator());
    }

    /**
     * Prints the closing XML elements of the RSS feed
     *
     * Closes the channel and rss tags to complete the XML document.
     * This method should be called after all feed entries have been printed.
     *
     * @return void
     */
    public function printClose(): void
    {
        $this->printFormat(1, '/channel');
        $this->printFormat(0, '/rss');
    }

    /**
     * Helper method to print formatted XML tags
     *
     * Handles proper indentation, CDATA wrapping, and array values.
     * This internal utility method ensures consistent XML formatting throughout the feed.
     *
     * @param int $indentLevel Number of indentation levels (each level = 2 spaces)
     * @param string $tag XML tag name without brackets
     * @param string|array|null $value Tag value (null for empty tags)
     * @param bool $cdata Whether to wrap the value in CDATA for escaping HTML content
     * @return void
     */
    private function printFormat(int $indentLevel, string $tag, $value = '', bool $cdata = false): void
    {
        if (empty($tag) || $value === null) {
            return;
        }

        $indent = str_repeat('  ', $indentLevel);

        if ($value === '') {
            // Empty tag (opening tag or self-closing tag)
            echo "$indent<$tag>" . PHP_EOL;
        } elseif (is_array($value)) {
            // Handle array values by creating multiple instances of the same tag
            foreach ($value as $v) {
                if ($cdata) {
                    $v = '<![CDATA[ ' . html_entity_decode($v) . ' ]]>';
                }
                echo "$indent<$tag>$v</$tag>" . PHP_EOL;
            }
        } else {
            // Standard tag with single value
            if ($cdata) {
                $value = '<![CDATA[ ' . html_entity_decode($value) . ' ]]>';
            }
            echo "$indent<$tag>$value</$tag>" . PHP_EOL;
        }
    }

    /**
     * Prints feed metadata in debug format
     *
     * Used when debug mode is enabled to display feed properties in a readable format.
     * This provides a human-friendly view of the feed configuration for troubleshooting.
     *
     * @return void
     */
    public function printOpenDebug(): void
    {
        $this->printFormatDebug('Feed Title:', $this->getTitle());
        $this->printFormatDebug('Feed Description:', $this->getDescription());
        $this->printFormatDebug('Feed Link:', $this->getLink());
        $this->printFormatDebug('Feed Language:', $this->getLanguage());
        $this->printFormatDebug('Feed Last Build Date:', $this->getLastBuildDate());
        $this->printFormatDebug('Feed Generator:', $this->getGenerator());
        echo PHP_EOL;
        $this->printFormatDebug('Feed Cache Location:', $this->getCacheLocation());
        echo PHP_EOL;
    }

    /**
     * Prints a debug line with proper formatting
     *
     * Creates consistently formatted debug output with aligned values.
     * Used by printOpenDebug and can be used by other debug methods.
     *
     * @param string $element Element name/label to display
     * @param mixed $value Element value (can be string or array)
     * @return void
     */
    public function printFormatDebug(string $element, $value = ''): void
    {
        if (empty($element) || $value === null) {
            return;
        }

        // Create consistent padding between element names and values
        $indent = str_repeat(' ', self::DEBUG_PADDING_LENGTH - strlen($element));

        if (is_array($value)) {
            // Handle array values by printing each on a new line with the same element name
            foreach ($value as $v) {
                echo $element . $indent . $v . PHP_EOL;
            }
        } else {
            // Standard single value output
            echo $element . $indent . $value . PHP_EOL;
        }
    }

    // Getter methods - provide access to private properties

    /**
     * Gets the feed cache location
     *
     * @return string|null Feed cache location
     */
    public function getCacheLocation(): ?string
    {
        return $this->cache_location;
    }

    /**
     * Gets the feed description
     *
     * The description explains the purpose and content of the feed.
     *
     * @return string|null Feed description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Gets the feed generator information
     *
     * Identifies the software used to generate the feed.
     *
     * @return string Generator string
     */
    public function getGenerator(): string
    {
        return $this->generator;
    }

    /**
     * Gets the feed language
     *
     * Specifies the language the feed is written in (e.g., 'en-us').
     *
     * @return string|null Feed language code
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Gets the last build date
     *
     * Indicates when the feed was last updated, in RSS date format.
     *
     * @return string|null Last build date in RSS format
     */
    public function getLastBuildDate(): ?string
    {
        return $this->lastBuildDate;
    }

    /**
     * Gets the feed links
     *
     * URLs to the HTML website corresponding to the feed.
     *
     * @return array|null Array of feed links
     */
    public function getLink(): ?array
    {
        return $this->link;
    }

    /**
     * Gets the feed title
     *
     * The name of the feed, typically matching the associated website.
     *
     * @return string|null Feed title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    // Setter methods - allow modification of private properties

    /**
     * Sets the feed cache location
     *
     * @param string|null $cache_location Feed cache location
     * @return void
     */
    public function setCacheLocation(?string $cache_location): void
    {
        $this->cache_location = $cache_location;
    }

    /**
     * Sets the feed description
     *
     * @param string|null $description Feed description
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Sets the feed language
     *
     * @param string|null $language Feed language code (e.g., 'en-us')
     * @return void
     */
    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    /**
     * Sets the feed links
     *
     * @param array|null $link Array of feed links
     * @return void
     */
    public function setLink(?array $link): void
    {
        if(is_array($link)) {
            $link = array_map('htmlspecialchars', $link);
        } else {
            $link = htmlspecialchars($link);
        }
        $this->link = $link;
    }

    /**
     * Sets the feed title
     *
     * @param string|null $title Feed title
     * @return void
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
