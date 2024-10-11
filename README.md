# FeedPiper

FeedPiper is a toolbox that provides two services:

* HTML2FEED	converts a page into a feed
* FEEDPIPER	merges feeds and filters them

### Usage

* HTML2FEED
  * https://DOMAIN/html2feed.php?page=feed_name
  * https://DOMAIN/html2feed.php?page=feed_name&debug=true

* FEEDPIPER
  * https://DOMAIN/feedpiper.php?feed=feed_name
  * https://DOMAIN/feedpiper.php?feed=feed_name&debug=true
  * https://DOMAIN/feedpiper.php?feed=feed_name&debug=true&entry=1

### Configuration

Configuration files are in the config folder.

* conf.php is the global application configuration
* html2feed.conf.php and feedfilter.conf.php are the individual application configurations

### Requirements

* PHP 7.2+
* Cache System (folder or db)
  * Writable cache folder (chmod 777)
  * MySQL/Maria DB
* Upload the project to your server

* Clone the feedpiper repository then the submodules
  * git clone https://github.com/kenijo/feedpiper
  * git submodule update --init --recursive

* Clone the feedpiper repository with the submodules all at once
  * git clone --recurse-submodules https://github.com/kenijo/feedpiper

* Add a new submodule to the feedpiper repository
  * git submodule add https://github.com/user/<strong>repo</strong> include/<strong>repo</strong>


### Credits

This project relies on the following projects
* Parsedown: Markdown Parser in PHP
  * [Official Site](https://parsedown.org/)
  * [On GitHub](https://github.com/erusev/parsedown)

* Simple HTML DOM: A HTML DOM parser written in PHP5+ let you manipulate HTML in a very easy way!
  * [Official Site](http://simplehtmldom.sourceforge.net/)
  * [On SourceForge](https://sourceforge.net/projects/simplehtmldom/)

* SimplePie: A simple Atom/RSS parsing library for PHP
  * [Official Site](http://simplepie.org/)
  * [On GitHub](https://github.com/simplepie/simplepie/)
