# FeedPiper

FeedPiper is a toolbox that provides two services:

* HTML2FEED converts a page into a feed
* FEEDPIPER merges feeds and filters them

## Usage

### HTML2FEED

* https://DOMAIN/html2feed.php?page=feed_name
* https://DOMAIN/html2feed.php?page=feed_name&debug=true

### FEEDPIPER

* https://DOMAIN/feedpiper.php?feed=feed_name
* https://DOMAIN/feedpiper.php?feed=feed_name&debug=true
* https://DOMAIN/feedpiper.php?feed=feed_name&debug=true&entry=1

## Configuration

Configuration files are in the config folder.

* conf.php is the global application configuration
* html2feed.conf.php and feedfilter.conf.php are the individual application configurations

## Requirements

* PHP 7.2+
* Cache System (folder or db)
  * Writable cache folder (chmod 777)
  * MySQL/MariaDB
* Upload the project to your server

* Clone the feedpiper repository
  * git clone https://github.com/kenijo/feedpiper

* Add the composer dependancies
  * composer install

* Update the composer dependancies
  * composer update

## Credits - This project relies on the following dependancies

- [Parsedown](https://parsedown.org/): Markdown Parser in PHP [(GitHub)](https://github.com/erusev/parsedown)

- [Simple HTML DOM](<http://simplehtmldom.sourceforge.net/>): HTML DOM parser in PHP [(GitHub)](https://github.com/simplehtmldom/simplehtmldom)

- [SimplePie](http://simplepie.org/): Atom/RSS parser in PHP [(GitHub)](https://github.com/simplepie/simplepie/)
