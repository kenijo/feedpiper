<?php

/**
 * FeedEntry Class
 *
 * This class handles individual feed entries (items) for the FeedPiper application.
 * It provides methods to set entry properties and generate properly formatted RSS item output.
 *
 * The FeedEntry class is responsible for:
 * - Managing individual feed entry properties (title, description, content, etc.)
 * - Filtering entries based on whitelist and blacklist rules
 * - Providing both normal and debug output modes for feed items
 * - Supporting standard RSS 2.0 item specifications with additional namespaces
 */
class FeedEntry
{
    // Constants for formatting
    private const DEBUG_PADDING_LENGTH = 35;                                    // Character padding for debug output formatting

    // Feed entry properties (core RSS item elements)
    private ?array $authors = null;         // Entry authors - names of content creators
    private ?array $blacklist = null;       // Blacklist rules - patterns that will exclude the entry if matched
    private ?array $categories = null;      // Entry categories - topics or keywords describing the entry
    private ?string $content = null;        // Entry content - full content of the entry (content:encoded)
    private ?string $description = null;    // Entry description - summary or excerpt of the entry
    private ?object $enclosure = null;      // Entry enclosure - attached media file (image, audio, video)
    private ?string $id = null;             // Entry ID/GUID - unique identifier for the entry
    private ?string $link = null;           // Entry link - URL to the full content on the source website
    private ?string $pubDate = null;        // Publication date - when the entry was published (RSS format)
    private bool $skip = false;             // Skip flag - whether to exclude this entry from the feed
    private ?string $title = null;          // Entry title - headline or name of the entry
    private ?array $whitelist = null;       // Whitelist rules - patterns that will include the entry if matched

    /**
     * Prints the feed entry in XML format
     *
     * Outputs a complete RSS item element with all available properties.
     * This method follows RSS 2.0 specifications with additional namespaces
     * for extended functionality (Dublin Core, content).
     *
     * @return void
     */
    public function print(): void
    {
        $this->printFormat(2, 'item');
        $this->printFormat(3, 'title', $this->getTitle(), true);

        // Create metadata section with authors and categories
        $metadata = null;
        $authors = $this->getAuthors() ? 'Authors: ' . implode(', ', $this->getAuthors()) : '';
        if ($authors) {
            $metadata .= '<br />' . $authors;
        }
        $categories = $this->getCategories() ? 'Categories: ' . implode(', ', $this->getCategories()) : '';
        if ($categories) {
            $metadata .= '<br />' . $categories;
        }

        // Add description with metadata if available
        if ($description = $this->getDescription()) {
            $description .= '<br />' . $metadata;
            $this->printFormat(3, 'description', $description, true);
        }

        // Add full content with metadata if available
        if ($content = $this->getContent()) {
            $content .= '<br />' . $metadata;
            $this->printFormat(3, 'content:encoded', $content, true);
        }

        // Add standard RSS item elements
        $this->printFormat(3, 'link', $this->getLink());
        $this->printFormat(3, 'guid', $this->getId());
        $this->printFormat(3, 'pubDate', $this->getPubDate());

        // Add extended elements using namespaces
        $this->printFormat(3, 'dc:creator', $this->getAuthors(), true);
        $this->printFormat(3, 'category', $this->getCategories(), true);

        // Add enclosure if available
        if ($enclosure = $this->getEnclosure()) {
            if ($enclosure->get_link()) {
                $attributes = ['enclosure'];

                if ($length = $enclosure->get_length()) {
                    $attributes[] = 'length="' . $length . '"';
                }

                $attributes[] = 'url="' . htmlspecialchars($enclosure->get_link()) . '"';

                if ($type = $enclosure->get_type()) {
                    $attributes[] = 'type="' . $type . '"';
                }

                $attributes[] = '/';

                $this->printFormat(3, implode(' ', $attributes));
            }
        }

        $this->printFormat(2, '/item');
    }

    /**
     * Helper method to print formatted XML tags
     *
     * Handles proper indentation, CDATA wrapping, and array values.
     * This internal utility method ensures consistent XML formatting throughout the feed.
     *
     * @param int $indentLevel Number of indentation levels (each level = 2 spaces)
     * @param string $tag XML tag name without brackets
     * @param string|array $value Tag value (null for empty tags)
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
            echo "$indent<$tag>" . PHP_EOL;
        } elseif (is_array($value)) {
            foreach ($value as $v) {
                if ($cdata) {
                    $v = '<![CDATA[ ' . html_entity_decode($v) . ' ]]>';
                }
                echo "$indent<$tag>$v</$tag>" . PHP_EOL;
            }
        } else {
            if ($cdata) {
                $value = '<![CDATA[ ' . html_entity_decode($value) . ' ]]>';
            }
            echo "$indent<$tag>$value</$tag>" . PHP_EOL;
        }
    }

    /**
     * Prints debug information for the entry
     *
     * Displays all entry properties in a human-readable format.
     * Used when debug mode is enabled to help troubleshoot feed issues.
     *
     * @return void
     */
    public function printDebug(): void
    {
        $this->printFormatDebug('Entry Title:', $this->getTitle());
        echo PHP_EOL;
        $this->printFormatDebug('Entry Description:', $this->getDescription());
        echo PHP_EOL;
        $this->printFormatDebug('Entry Content:', $this->getContent());
        echo PHP_EOL;
        $this->printFormatDebug('Entry Link:', $this->getLink());
        $this->printFormatDebug('Entry GUID:', $this->getId());
        $this->printFormatDebug('Entry Pub Date:', $this->getPubDate());
        echo PHP_EOL;
        $this->printFormatDebug('Entry Authors:', implode(', ', $this->getAuthors() ?? []));
        $this->printFormatDebug('Entry Categories:', implode(', ', $this->getCategories() ?? []));
        echo PHP_EOL;

        $enclosure = $this->getEnclosure();
        if ($enclosure && $enclosure->get_link()) {
            $this->printFormatDebug('Entry Enclosure Link:', htmlspecialchars($enclosure->get_link()));
            $this->printFormatDebug('Entry Enclosure Type:', $enclosure->get_type());
            $this->printFormatDebug('Entry Enclosure Length:', $enclosure->get_length());
            echo PHP_EOL;
        }

        $this->printDebugList('Blacklist', $this->getBlacklist());
        $this->printDebugList('Whitelist', $this->getWhitelist());

        $this->printFormatDebug('SKIP ENTRY:', $this->getSkip() ? 'TRUE' : 'FALSE');
    }

    /**
     * Prints debug information for a list (whitelist or blacklist)
     *
     * Formats and displays filtering rules in a readable format.
     * Used by printDebug to show which rules are being applied.
     *
     * @param string $listType The type of list (Whitelist or Blacklist)
     * @param array|null $list The list to print
     * @return void
     */
    private function printDebugList(string $listType, ?array $list): void
    {
        if (empty($listType) || empty($list)) {
            return;
        }

        foreach ($list as $type => $conditions) {
            foreach ($conditions as $condition => $value) {
                $this->printFormatDebug(
                    "$listType " . ucfirst($type) . ' ' . strtoupper($condition) . ':',
                    implode(', ', $value)
                );
            }
            echo PHP_EOL;
        }
    }

    /**
     * Prints a debug line with proper formatting
     *
     * Creates consistently formatted debug output with aligned values.
     * Used by printDebug and can be used by other debug methods.
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

        $indent = str_repeat(' ', self::DEBUG_PADDING_LENGTH - strlen($element));
        if (is_array($value)) {
            foreach ($value as $v) {
                echo $element . $indent . $v . PHP_EOL;
            }
        } else {
            echo $element . $indent . $value . PHP_EOL;
        }
    }

    /**
     * Creates categories from the entry link
     *
     * Extracts path components from the URL and converts them to categories.
     * This helps generate meaningful categories when none are provided.
     *
     * @return array The categories created from the link
     */
    private function createCategoriesFromLink(): array
    {
        if (!$link = $this->getLink()) {
            return [];
        }

        $link = urldecode($link);
        $link = substr($link, strpos($link, '//') + 2);
        $link = substr($link, strpos($link, '/') + 1);
        $link = substr($link, 0, strrpos($link, '/'));
        $link = parse_url($link)['path'] ?? '';

        $arrLink = explode('/', $link);
        foreach ($arrLink as &$value) {
            $value = preg_replace('#' . '[[:punct:]]' . '#imu', ' ', $value);
        }
        return $arrLink;
    }

    /**
     * Creates categories from the entry title
     *
     * Extracts meaningful parts from the title and converts them to categories.
     * Looks for patterns like colons, brackets, etc. that often indicate categories.
     *
     * @return array The categories created from the title
     */
    private function createCategoriesFromTitle(): array
    {
        if (!$title = $this->getTitle()) {
            return [];
        }

        $title = html_entity_decode($title);
        $title = strip_tags($title);
        $title = removeAccents($title);

        $colon = before(':', $title);
        $dot = before('.', $title);
        $bracket = between('[', ']', $title);
        $curly = between('{', '}', $title);

        $title = $colon . ' ' . $dot . ' ' . $bracket . ' ' . $curly;
        $title = preg_replace('#' . '[[:punct:]]' . '#imu', ' ', $title);

        return explode(' ', $title);
    }

    /**
     * Filters entries based on whitelist and blacklist
     *
     * Applies filtering rules to determine if an entry should be included or excluded.
     * Whitelist rules take precedence over blacklist rules.
     *
     * @param array $entries The entries to filter (key-value pairs of entry types and their values)
     * @return void
     */
    public function filterEntries(): void
    {
        $entries = [
            'author' => $this->getAuthors(),
            'category' => $this->getCategories(),
            'content' => $this->getContent() ?: $this->getDescription(),
            'title' => $this->getTitle()
        ];

        // Check all entries against whitelist first
        $this->setSkip(true);
        foreach ($entries as $entryType => $entryValues) {
            if (is_string($entryValues)) {
                $entryValues = [$entryValues];
            }

            if (empty($entryValues)) {
                continue;
            }

            foreach ($entryValues as $entryValue) {
                if (!empty($this->getWhitelist())) {
                    $found = $this->checkEntryAgainstList($entryValue, $entryType, $this->getWhitelist());
                    if ($found === true) {
                        // If we found a match, we stop checking and keep the entry
                        $this->setSkip(false);
                        return;
                    }
                }
            }

        }

        // Then check all entries against blacklist
        $this->setSkip(false);
        foreach ($entries as $entryType => $entryValues) {
            if (is_string($entryValues)) {
                $entryValues = [$entryValues];
            }

            if (empty($entryValues)) {
                continue;
            }

            foreach ($entryValues as $entryValue) {
                if (!empty($this->getBlacklist())) {
                    $found = $this->checkEntryAgainstList($entryValue, $entryType, $this->getBlacklist());
                    if ($found === true) {
                        // If we found a match, we stop checking and skip the entry
                        $this->setSkip(true);
                        return;
                    }
                }
            }
        }
    }

    /**
     * Checks an entry value against a list (whitelist or blacklist)
     *
     * Applies different matching conditions (starts, contains, ends, regex)
     * to determine if an entry matches the filtering rules.
     *
     * @param string $entryValue The entry value to check
     * @param string $entryType The type of entry (title, content, etc.)
     * @param array $list The list to check against
     * @return bool True if the entry matches the list, false otherwise
     */
    private function checkEntryAgainstList(string $entryValue, string $entryType, array $list): bool
    {
        if ($entryType == 'content' && $entryValue == '') {
            return true;
        }

        $entryValue = html_entity_decode($entryValue);
        $entryValue = strip_tags($entryValue);
        $entryValue = removeAccents($entryValue);
        $entryValue = strtolower($entryValue);

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
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    // Getter methods

    /**
     * Gets the blacklist filter rules
     *
     * Returns the array of terms used to filter out unwanted content.
     *
     * @return array|null Array of blacklist terms
     */
    public function getBlacklist(): ?array
    {
        return $this->blacklist;
    }

    /**
     * Gets the entry authors
     *
     * Returns the list of authors and contributors associated with this entry.
     *
     * @return array|null Array of author names/emails
     */
    public function getAuthors(): ?array
    {
        return $this->authors;
    }

    /**
     * Gets the entry categories
     *
     * Returns the list of categories/tags associated with this entry.
     *
     * @return array|null Array of category terms
     */
    public function getCategories(): ?array
    {
        return $this->categories;
    }

    /**
     * Gets the entry content
     *
     * Returns the full content of the entry, which may include HTML formatting.
     *
     * @return string|null Entry content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Gets the entry description
     *
     * Returns the summary or excerpt of the entry content.
     *
     * @return string|null Entry description/summary
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Gets the entry enclosure
     *
     * Returns the media attachment (image, audio, etc.) associated with this entry.
     *
     * @return object|null SimplePie enclosure object
     */
    public function getEnclosure(): ?object
    {
        return $this->enclosure;
    }

    /**
     * Gets the entry ID
     *
     * Returns the unique identifier for this entry, typically a permalink URL.
     *
     * @return string|null Entry ID/permalink
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Gets the entry link
     *
     * Returns the URL to the original content this entry represents.
     *
     * @return string|null Entry URL
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * Gets the entry publication date
     *
     * Returns the date when this entry was published, in RSS format.
     *
     * @return string|null Publication date in RSS format
     */
    public function getPubDate(): ?string
    {
        return $this->pubDate;
    }

    /**
     * Gets the entry skip status
     *
     * Indicates whether this entry should be excluded from the feed output.
     *
     * @return bool True if entry should be skipped, false otherwise
     */
    public function getSkip(): bool
    {
        return $this->skip;
    }

    /**
     * Gets the entry title
     *
     * Returns the headline or title of this entry.
     *
     * @return string|null Entry title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Gets the whitelist filter rules
     *
     * Returns the array of terms used to explicitly include content.
     *
     * @return array|null Array of whitelist terms
     */
    public function getWhitelist(): ?array
    {
        return $this->whitelist;
    }

    // Setter methods

    /**
     * Sets the blacklist for filtering the feed entry
     *
     * Defines terms that will cause an entry to be excluded if matched.
     * Used in conjunction with filtering logic to remove unwanted content.
     *
     * @param array|null $blacklist Array of blacklist terms
     * @return void
     */
    public function setBlacklist(?array $blacklist): void
    {
        $this->blacklist = $blacklist;
    }

    /**
     * Sets the authors and contributors for the feed entry
     *
     * Processes SimplePie author and contributor objects to extract names and emails.
     * Combines both authors and contributors into a single clean array.
     *
     * @param array|null $authors List of author objects from SimplePie
     * @param array|null $contributors List of contributor objects from SimplePie
     * @return void
     */
    public function setAuthors(?array $authors, ?array $contributors): void
    {
        $arrAuthors = [];

        // Process authors
        if ($authors) {
            foreach ($authors as $author) {
                $arrAuthors[] = $author->get_name();
                $arrAuthors[] = $author->get_email();
            }
        }

        // Process contributors
        if ($contributors) {
            foreach ($contributors as $contributor) {
                $arrAuthors[] = $contributor->get_name();
                $arrAuthors[] = $contributor->get_email();
            }
        }

        $this->authors = cleanArray($arrAuthors);
    }

    /**
     * Sets the categories for the feed entry
     *
     * Processes SimplePie category objects to extract terms and labels.
     * If no categories are provided, attempts to generate them from the entry link.
     * Filters out short, numeric-only, or pipe-containing categories.
     *
     * @param array|null $categories List of category objects from SimplePie
     * @return void
     */
    public function setCategories(?array $categories): void
    {
        $arrCategories = [];

        // Process provided categories
        if ($categories) {
            foreach ($categories as $category) {
                $arrCategories[] = $category->get_label();
                $arrCategories[] = $category->get_term();
            }
        }

        $arrCategories = array_filter($arrCategories);

        // If no categories defined, generate from link and/or title
        if (empty($arrCategories)) {
            $arrCategories = $this->createCategoriesFromLink();     // Create categories from links
            //$arrCategories = $this->createCategoriesFromTitle();    // Create categories from title

            // Filter out short or numeric-only categories
            $arrCategories = array_filter($arrCategories, function ($item) {
                return strlen($item) > 3 && !preg_match('#' . '\d+' . '#imu', $item);
            });
        }
        // Remove categories with a pipe character (i.e. engadget feeds )
        $arrCategories = array_filter($arrCategories, fn($item) => strpos($item, '|') === false);

        // Clean and format the categories
        $this->categories = cleanArray($arrCategories, 'ucwords');
    }

    /**
     * Sets the content of the feed entry
     *
     * Defines the full content/body of the entry, which may include HTML.
     * This is typically the complete article content.
     *
     * @param string $content The entry content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Sets the description of the feed entry
     *
     * Defines the summary or excerpt of the entry content.
     * This is typically a shorter version of the full content.
     *
     * @param string $description The entry description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Sets the enclosure for the feed entry
     *
     * Defines a media attachment (image, audio, etc.) associated with this entry.
     * Enclosures are used for podcasts, featured images, and other media.
     *
     * @param object|null $enclosure The SimplePie enclosure object
     * @return void
     */
    public function setEnclosure($enclosure): void
    {
        $this->enclosure = $enclosure;
    }

    /**
     * Sets the ID of the feed entry
     *
     * Defines the unique identifier for this entry, typically a permalink URL.
     * This ID should remain consistent across feed updates.
     *
     * @param string $id The entry ID
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Sets the link of the feed entry
     *
     * Defines the URL to the original content this entry represents.
     * This link allows readers to view the full content on the source website.
     *
     * @param string $link The entry link URL
     * @return void
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * Sets the publication date of the feed entry
     *
     * Defines when this entry was published, in RSS format.
     * This helps readers determine the freshness of the content.
     *
     * @param string|\DateTime $pubDate The entry publication date
     * @return void
     */
    public function setPubDate($pubDate): void
    {
        $this->pubDate = $pubDate;
    }

    /**
     * Sets whether the entry should be skipped
     *
     * Controls whether this entry should be excluded from the feed output.
     * Used by filtering logic to remove entries that don't match criteria.
     *
     * @param bool $skip Whether to skip this entry
     * @return void
     */
    public function setSkip(bool $skip): void
    {
        $this->skip = $skip;
    }

    /**
     * Sets the title of the feed entry
     *
     * Defines the headline or title of this entry.
     * This is typically the most prominent text displayed in feed readers.
     *
     * @param string $title The entry title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Sets the whitelist for filtering the feed entry
     *
     * Defines terms that will cause an entry to be included if matched.
     * Used in conjunction with filtering logic to prioritize wanted content.
     *
     * @param array|null $whitelist Array of whitelist terms
     * @return void
     */
    public function setWhitelist(?array $whitelist): void
    {
        $this->whitelist = $whitelist;
    }
}
