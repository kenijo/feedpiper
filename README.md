# FeedPiper

FeedPiper is a toolbox that provides two services:

* HTML2FEED	converts a page into a feed
* FEEDPIPER	merges feeds and filters them

It is aimed at replacing Yahoo Pipes in the way I was using it: merging and filtering feeds.

### Usage

* HTML2FEED
  * http://www.domain.com/html2feed.php?page=example
  * http://www.domain.com/html2feed.php?page=example&debug=true

* FEEDPIPER
  * http://www.domain.com/feedpiper.php?feed=example
  * http://www.domain.com/feedpiper.php?feed=example&debug=true

### Requirements

* PHP 5.3+
* Cache System (folder or db)
  * cache folder writable (0766)  
  * MySQL/Maria DB
* Upload the project to your server
* Configure it

### Configuration

Configuration files are in the config folder.
Make a copy of the config files as local.*.php
This will allow you to have your own configuration files that will not be commited to GitHub

* conf.php is the global application configuration
* html2feed.conf.php and feedfilter.conf.php are the individual application configurations

### Credits

This project relies on the following projects
* SimplePie: A simple Atom/RSS parsing library for PHP
  * [Official Site](http://simplepie.org/)
  * [On GitHub](https://github.com/simplepie/simplepie/)

* Simple HTML DOM: A HTML DOM parser written in PHP5+ let you manipulate HTML in a very easy way!
  * [Official Site](http://simplehtmldom.sourceforge.net/)
  * [On SourceForge](https://sourceforge.net/projects/simplehtmldom/)
