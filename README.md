# FeedPiper

FeedPiper is a toolbox that provides two services:

* FEED merges feeds and filters them
* HTML2FEED converts a page into a feed

## Usage

### FEEDPIPER

* https://DOMAIN/feed.php?feed=feed_name
* https://DOMAIN/feed.php?feed=feed_name&debug=true
* https://DOMAIN/feed.php?feed=feed_name&debug=true&entry=2

### HTML2FEED

* https://DOMAIN/html2feed.php?page=feed_name
* https://DOMAIN/html2feed.php?page=feed_name&debug=true

## Configuration

Configuration files are in the config folder.

* Rename `feedfilter.conf.default.php` to `feedfilter.conf.php` to configure `feed.php` feeds and and filters.

* Rename `html2feed.conf.default.php` to `html2feed.conf.php` to configure `html2feed.php` websites to convert into feeds.

## Requirements

* PHP 7.4+
* Writable cache folder (chmod 777)
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
