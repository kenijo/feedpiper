<?php

/**
 * Inclusion of all the configuration and library files
 */
require_once './config/conf.php';
require_once './config/feedfilter.conf.php';
require_once './config/html2feed.conf.php';

require_once 'func.array.php';
require_once 'func.rest.php';
require_once 'func.string.php';
require_once 'func.url.php';

require_once 'class.entry.php';
require_once 'class.feed.php';
require_once 'class.filteredfeed.php';

// Include SimplePie
require_once 'simplepie/autoloader.php';
require_once 'simplepie/idn/idna_convert.class.php';

// Include Simple HTML DOM from Sourceforge
require_once 'simple_html_dom/src/KubAT/PhpSimple/lib/simple_html_dom.php';

// Include Parsedown
require_once 'include/parsedown/Parsedown.php';

// Include the local configuration files that will override those settings if they exists.
// This prevents committing your own configuration to GitHub
@include_once './config/local.conf.php';
@include_once './config/local.feedfilter.conf.php';
@include_once './config/local.html2feed.conf.php';
