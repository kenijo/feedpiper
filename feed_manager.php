<?php
// Initialize session for flash messages
session_start();

// Path to the config file
$configFile = __DIR__ . '/config/feedfilter.conf.php';

// Function to safely read the config file
function readConfigFile($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }

    // Include the file and capture the variables
    include $filePath;

    // Return the config variables
    return [
        'useCurl' => isset($useCurl) ? $useCurl : true,
        'globalBlacklist' => isset($globalBlacklist) ? $globalBlacklist : [],
        'feedConf' => isset($feedConf) ? $feedConf : []
    ];
}

// Function to save the config file
function saveConfigFile($filePath, $useCurl, $globalBlacklist, $feedConf)
{
    // Create the PHP code to write to the file
    $content = "<?php\n\n";
    $content .= "/**\n";
    $content .= " * Force SimplePie to use fsockopen() instead of cURL\n";
    $content .= " * If cURL doesn't work, set variable to false to use fsockopen()\n";
    $content .= " * Default value is true\n";
    $content .= " */\n";
    $content .= "\$useCurl = " . ($useCurl ? 'true' : 'false') . ";\n\n";

    // Add global blacklist
    $content .= "/**\n";
    $content .= " * Global filters, works as an anti-spam\n";
    $content .= " * Skip any feed that contains the following keywords\n";
    $content .= " * Blacklist: exclude entries matching the rule (no global whitelist)\n";
    $content .= "*/\n";
    $content .= "\$globalBlacklist = " . var_export($globalBlacklist, true) . ";\n\n";

    $content .= "/**\n";
    $content .= " * ATOM Filter Configuration\n";
    $content .= " *\n";
    $content .= " * Used to merge feeds together and output a single feed.\n";
    $content .= " * As well as filtering feed entries.\n";
    $content .= " *\n";
    $content .= " * Fields that can be filtered:\n";
    $content .= " *   title\n";
    $content .= " *   content\n";
    $content .= " *   author\n";
    $content .= " *   category\n";
    $content .= " *\n";
    $content .= " * Filtering matches the following expressions:\n";
    $content .= " *   starts   : equivalent to regex ^(.*)\n";
    $content .= " *   contains : equivalent to regex \\b(.*)\\b\n";
    $content .= " *   ends     : equivalent to regex (.*)$\n";
    $content .= " *   regex    : any regex\n";
    $content .= " *\n";
    $content .= " * NOTE: To use this configuration, call it with:\n";
    $content .= " *   https://DOMAIN/feedfilter.php?feed=feed_name\n";
    $content .= " *\n";
    $content .= " * Debug mode:\n";
    $content .= " *   https://DOMAIN/feedfilter.php?feed=feed_name&debug=true\n";
    $content .= " *   https://DOMAIN/feedfilter.php?feed=feed_name&debug=true&entry=1\n";
    $content .= " */\n\n";
    $content .= "\$feedConf = [\n";

    // Add globalBlacklist to feedConf
    $content .= "    'globalBlacklist' => [\n";
    $content .= "        'category' => ['contains' => \$globalBlacklist],\n";
    $content .= "        'title' => ['contains' => \$globalBlacklist],\n";
    $content .= "    ],\n\n";

    // Add each feed
    foreach ($feedConf as $feedName => $feedConfig) {
        if ($feedName === 'globalBlacklist')
            continue;

        $content .= "    '$feedName' => [\n";
        $content .= "        'title' => '" . addslashes($feedConfig['title']) . "',\n";

        // Add URLs
        $content .= "        'url' => [\n";
        foreach ($feedConfig['url'] as $url) {
            $content .= "            '" . addslashes($url) . "',\n";
        }
        $content .= "        ],\n";

        // Add whitelist
        $content .= "        // Whitelist: never exclude an entry matching the rules (executed before blacklisting)\n";
        $content .= "        'whitelist' => " . var_export($feedConfig['whitelist'] ?? [], true) . ",\n";

        // Add blacklist
        $content .= "        // Blacklist: always exclude an entry matching the rules (executed after whitelisting if no whitelist match was found)\n";
        $content .= "        'blacklist' => " . var_export($feedConfig['blacklist'] ?? [], true) . ",\n";

        $content .= "    ],\n";
    }

    $content .= "];\n";

    // Write to file
    return file_put_contents($filePath, $content);
}

// Load the current configuration
$config = readConfigFile($configFile);
if ($config === false) {
    die("Error: Could not read the configuration file.");
}

$useCurl = $config['useCurl'];
$globalBlacklist = $config['globalBlacklist'];
$feedConf = $config['feedConf'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save feed
    if (isset($_POST['action']) && $_POST['action'] === 'save_feed') {
        $feedName = $_POST['feed_name'];
        $originalName = $_POST['original_name'];
        $feedTitle = $_POST['feed_title'];
        $feedUrls = explode("\n", $_POST['feed_urls']);
        $feedUrls = array_map('trim', $feedUrls);
        $feedUrls = array_filter($feedUrls);

        // Prepare the feed configuration
        $feedConfig = [
            'title' => $feedTitle,
            'url' => $feedUrls,
            'whitelist' => [],
            'blacklist' => []
        ];

        // Process whitelist
        foreach (['author', 'category', 'content', 'title'] as $field) {
            foreach (['starts', 'contains', 'ends', 'regex'] as $condition) {
                $key = "whitelist_{$field}_{$condition}";
                if (isset($_POST[$key]) && !empty($_POST[$key])) {
                    $values = explode("\n", $_POST[$key]);
                    $values = array_map('trim', $values);
                    $values = array_filter($values);

                    if (!empty($values)) {
                        if (!isset($feedConfig['whitelist'][$field])) {
                            $feedConfig['whitelist'][$field] = [];
                        }
                        $feedConfig['whitelist'][$field][$condition] = $values;
                    }
                }
            }
        }

        // Process blacklist
        foreach (['author', 'category', 'content', 'title'] as $field) {
            foreach (['starts', 'contains', 'ends', 'regex'] as $condition) {
                $key = "blacklist_{$field}_{$condition}";
                if (isset($_POST[$key]) && !empty($_POST[$key])) {
                    $values = explode("\n", $_POST[$key]);
                    $values = array_map('trim', $values);
                    $values = array_filter($values);

                    if (!empty($values)) {
                        if (!isset($feedConfig['blacklist'][$field])) {
                            $feedConfig['blacklist'][$field] = [];
                        }
                        $feedConfig['blacklist'][$field][$condition] = $values;
                    }
                }
            }
        }

        // If it's a rename, remove the old feed
        if ($feedName !== $originalName && !empty($originalName)) {
            unset($feedConf[$originalName]);
        }

        // Update the feed configuration
        $feedConf[$feedName] = $feedConfig;

        // Save the configuration
        if (saveConfigFile($configFile, $useCurl, $globalBlacklist, $feedConf)) {
            $_SESSION['flash_message'] = "Feed '$feedName' saved successfully.";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Error saving feed '$feedName'.";
            $_SESSION['flash_type'] = 'error';
        }

        // Reload the configuration
        $config = readConfigFile($configFile);
        $useCurl = $config['useCurl'];
        $globalBlacklist = $config['globalBlacklist'];
        $feedConf = $config['feedConf'];

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Delete feed
    if (isset($_POST['action']) && $_POST['action'] === 'delete_feed') {
        $feedName = $_POST['feed_name'];

        if (isset($feedConf[$feedName])) {
            unset($feedConf[$feedName]);

            // Save the configuration
            if (saveConfigFile($configFile, $useCurl, $globalBlacklist, $feedConf)) {
                $_SESSION['flash_message'] = "Feed '$feedName' deleted successfully.";
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Error deleting feed '$feedName'.";
                $_SESSION['flash_type'] = 'error';
            }

            // Reload the configuration
            $config = readConfigFile($configFile);
            $useCurl = $config['useCurl'];
            $globalBlacklist = $config['globalBlacklist'];
            $feedConf = $config['feedConf'];
        }

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Save global settings
    if (isset($_POST['action']) && $_POST['action'] === 'save_global') {
        $useCurl = isset($_POST['use_curl']) && $_POST['use_curl'] === '1';

        // Process global blacklist
        if (isset($_POST['global_blacklist']) && !empty($_POST['global_blacklist'])) {
            $values = explode("\n", $_POST['global_blacklist']);
            $values = array_map('trim', $values);
            $values = array_filter($values);
            $globalBlacklist = $values;
        } else {
            $globalBlacklist = [];
        }

        // Save the configuration
        if (saveConfigFile($configFile, $useCurl, $globalBlacklist, $feedConf)) {
            $_SESSION['flash_message'] = "Global settings saved successfully.";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Error saving global settings.";
            $_SESSION['flash_type'] = 'error';
        }

        // Reload the configuration
        $config = readConfigFile($configFile);
        $useCurl = $config['useCurl'];
        $globalBlacklist = $config['globalBlacklist'];
        $feedConf = $config['feedConf'];

        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get the selected feed
$selectedFeed = isset($_GET['feed']) ? $_GET['feed'] : '';
$feedData = null;

if (!empty($selectedFeed) && isset($feedConf[$selectedFeed])) {
    $feedData = $feedConf[$selectedFeed];
}

// Function to get filter values as a string
function getFilterValues($filters, $field, $condition)
{
    if (isset($filters[$field][$condition])) {
        return implode("\n", $filters[$field][$condition]);
    }
    return '';
}

// Get feed names for the select dropdown
$feedNames = array_keys($feedConf);
sort($feedNames);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feed Manager</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                color: #333;
            }

            h1,
            h2,
            h3 {
                color: #444;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
            }

            .flash-message {
                padding: 10px;
                margin-bottom: 20px;
                border-radius: 4px;
            }

            .flash-success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }

            .flash-error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }

            .nav {
                display: flex;
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }

            .nav a {
                margin-right: 15px;
                text-decoration: none;
                color: #007bff;
                padding: 5px 10px;
            }

            .nav a:hover {
                text-decoration: underline;
            }

            .nav a.active {
                background-color: #007bff;
                color: white;
                border-radius: 4px;
            }

            form {
                margin-bottom: 30px;
            }

            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }

            input[type="text"],
            select,
            textarea {
                width: 100%;
                padding: 8px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }

            textarea {
                min-height: 100px;
            }

            button {
                background-color: #007bff;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
            }

            button:hover {
                background-color: #0069d9;
            }

            button.delete {
                background-color: #dc background-color: #dc3545;
            }

            button.delete:hover {
                background-color: #c82333;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .filter-section {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }

            .filter-group {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
                margin-bottom: 15px;
            }

            .filter-item {
                margin-bottom: 10px;
            }

            .tabs {
                display: flex;
                margin-bottom: 15px;
                border-bottom: 1px solid #ddd;
            }

            .tab {
                padding: 10px 15px;
                cursor: pointer;
                margin-right: 5px;
                border: 1px solid transparent;
                border-bottom: none;
            }

            .tab.active {
                border-color: #ddd;
                border-bottom-color: white;
                border-radius: 4px 4px 0 0;
                margin-bottom: -1px;
                background-color: white;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>Feed Manager</h1>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="flash-message flash-<?php echo $_SESSION['flash_type']; ?>">
                    <?php echo $_SESSION['flash_message']; ?>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <div class="nav">
                <a href="?view=feeds"
                    class="<?php echo (!isset($_GET['view']) || $_GET['view'] === 'feeds') ? 'active' : ''; ?>">Manage
                    Feeds</a>
                <a href="?view=global"
                    class="<?php echo (isset($_GET['view']) && $_GET['view'] === 'global') ? 'active' : ''; ?>">Global
                    Settings</a>
            </div>

            <?php if (!isset($_GET['view']) || $_GET['view'] === 'feeds'): ?>
                <div class="feeds-section">
                    <h2>Feeds</h2>

                    <div class="form-group">
                        <label for="feed-select">Select a feed to edit:</label>
                        <select id="feed-select" onchange="location = this.value;">
                            <option value="?view=feeds">-- Select a feed --</option>
                            <?php foreach ($feedNames as $name): ?>
                                <?php if ($name !== 'globalBlacklist'): ?>
                                    <option value="?view=feeds&feed=<?php echo urlencode($name); ?>" <?php echo ($selectedFeed === $name) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button onclick="location.href='?view=feeds&feed=new'">Add New Feed</button>

                    <?php if ($selectedFeed === 'new' || (!empty($feedData) && $selectedFeed !== 'globalBlacklist')): ?>
                        <h3><?php echo ($selectedFeed === 'new') ? 'Add New Feed' : 'Edit Feed: ' . htmlspecialchars($selectedFeed); ?>
                        </h3>

                        <form method="post" action="">
                            <input type="hidden" name="action" value="save_feed">
                            <input type="hidden" name="original_name" value="<?php echo htmlspecialchars($selectedFeed); ?>">

                            <div class="form-group">
                                <label for="feed_name">Feed Name (used in URL):</label>
                                <input type="text" id="feed_name" name="feed_name"
                                    value="<?php echo ($selectedFeed !== 'new') ? htmlspecialchars($selectedFeed) : ''; ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="feed_title">Feed Title:</label>
                                <input type="text" id="feed_title" name="feed_title"
                                    value="<?php echo isset($feedData['title']) ? htmlspecialchars($feedData['title']) : ''; ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="feed_urls">Feed URLs (one per line):</label>
                                <textarea id="feed_urls" name="feed_urls"
                                    required><?php echo isset($feedData['url']) ? htmlspecialchars(implode("\n", $feedData['url'])) : ''; ?></textarea>
                            </div>

                            <div class="tabs">
                                <div class="tab active" data-tab="whitelist">Whitelist</div>
                                <div class="tab" data-tab="blacklist">Blacklist</div>
                            </div>

                            <div id="whitelist" class="tab-content active">
                                <div class="filter-section">
                                    <h3>Whitelist Filters</h3>
                                    <p>Entries matching these filters will always be included (executed before blacklisting)</p>

                                    <?php foreach (['author', 'category', 'content', 'title'] as $field): ?>
                                        <h4><?php echo ucfirst($field); ?></h4>
                                        <div class="filter-group">
                                            <?php foreach (['starts', 'contains', 'ends', 'regex'] as $condition): ?>
                                                <div class="filter-item">
                                                    <label
                                                        for="whitelist_<?php echo $field; ?>_<?php echo $condition; ?>"><?php echo ucfirst($condition); ?>:</label>
                                                    <textarea id="whitelist_<?php echo $field; ?>_<?php echo $condition; ?>"
                                                        name="whitelist_<?php echo $field; ?>_<?php echo $condition; ?>"><?php echo isset($feedData['whitelist']) ? getFilterValues($feedData['whitelist'], $field, $condition) : ''; ?></textarea>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div id="blacklist" class="tab-content">
                                <div class="filter-section">
                                    <h3>Blacklist Filters</h3>
                                    <p>Entries matching these filters will be excluded (executed after whitelisting if no
                                        whitelist
                                        match was found)</p>

                                    <?php foreach (['author', 'category', 'content', 'title'] as $field): ?>
                                        <h4><?php echo ucfirst($field); ?></h4>
                                        <div class="filter-group">
                                            <?php foreach (['starts', 'contains', 'ends', 'regex'] as $condition): ?>
                                                <div class="filter-item">
                                                    <label
                                                        for="blacklist_<?php echo $field; ?>_<?php echo $condition; ?>"><?php echo ucfirst($condition); ?>:</label>
                                                    <textarea id="blacklist_<?php echo $field; ?>_<?php echo $condition; ?>"
                                                        name="blacklist_<?php echo $field; ?>_<?php echo $condition; ?>"><?php echo isset($feedData['blacklist']) ? getFilterValues($feedData['blacklist'], $field, $condition) : ''; ?></textarea>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit">Save Feed</button>

                                <?php if ($selectedFeed !== 'new'): ?>
                                    <button type="submit" class="delete" name="action" value="delete_feed"
                                        onclick="return confirm('Are you sure you want to delete this feed?');">Delete Feed</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php elseif ($_GET['view'] === 'global'): ?>
                <div class="global-section">
                    <h2>Global Settings</h2>

                    <form method="post" action="">
                        <input type="hidden" name="action" value="save_global">

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="use_curl" value="1" <?php echo $useCurl ? 'checked' : ''; ?>>
                                Use cURL instead of fsockopen
                            </label>
                            <p><small>If cURL doesn't work, uncheck to use fsockopen()</small></p>
                        </div>

                        <div class="filter-section">
                            <h3>Global Blacklist</h3>
                            <p>Skip any feed that contains the following keywords (one per line)</p>
                            <div class="form-group">
                                <label for="global_blacklist">Keywords:</label>
                                <textarea id="global_blacklist"
                                    name="global_blacklist"><?php echo implode("\n", $globalBlacklist); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit">Save Global Settings</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <script>
            // Tab functionality
            document.addEventListener('DOMContentLoaded', function () {
                const tabs = document.querySelectorAll('.tab');
                tabs.forEach(tab => {
                    tab.addEventListener('click', function () {
                        // Remove active class from all tabs and content
                        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                        // Add active class to clicked tab and corresponding content
                        this.classList.add('active');
                        const tabId = this.getAttribute('data-tab');
                        document.getElementById(tabId).classList.add('active');
                    });
                });

                // Hide flash messages after 5 seconds
                const flashMessage = document.querySelector('.flash-message');
                if (flashMessage) {
                    setTimeout(() => {
                        flashMessage.style.display = 'none';
                    }, 5000);
                }
            });
        </script>
    </body>

</html>
