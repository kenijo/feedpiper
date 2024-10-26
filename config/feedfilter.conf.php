<?php

// Global Filter
$feedfilter['global']['blacklist']['category']['contains'] = ['athletisme', 'baseball', 'basket', 'basketball', 'cartoon', 'comic con', 'comic', 'coupe davis', 'coupe de france', 'coupe du monde', 'cyclisme', 'f1', 'fandom', 'fandoms', 'fed cup', 'fff', 'fifa', 'foot', 'football', 'formule1', 'golf', 'jeux olympiques', 'les bleus', 'ligue 1', 'ligue 2', 'ligue des champions', 'ligue europa', 'mariners', 'mercato', 'mlb', 'mls', 'natation', 'nba', 'nfl', 'nhl', 'pga', 'podcast', 'podcasts', 'psg', 'replay', 'rugby', 'seahawk', 'seahawks', 'seattle storm', 'ski', 'soccer', 'sounders', 'spoiler', 'spoilers', 'sponsor', 'sponsored', 'sponsors', 'sport', 'sports', 'tennis', 'tournoi de bercy', 'tournoi des vi nations', 'uefa', 'vendee globe', 'watch live', 'world cup', 'xv de france', 'xv'];
$feedfilter['global']['blacklist']['title']['contains']    = ['athletisme', 'baseball', 'basket', 'basketball', 'cartoon', 'comic con', 'comic', 'coupe davis', 'coupe de france', 'coupe du monde', 'cyclisme', 'f1', 'fandom', 'fandoms', 'fed cup', 'fff', 'fifa', 'foot', 'football', 'formule1', 'golf', 'jeux olympiques', 'les bleus', 'ligue 1', 'ligue 2', 'ligue des champions', 'ligue europa', 'mariners', 'mercato', 'mlb', 'mls', 'natation', 'nba', 'nfl', 'nhl', 'pga', 'podcast', 'podcasts', 'psg', 'replay', 'rugby', 'seahawk', 'seahawks', 'seattle storm', 'ski', 'soccer', 'sounders', 'spoiler', 'spoilers', 'sponsor', 'sponsored', 'sponsors', 'sport', 'sports', 'tennis', 'tournoi de bercy', 'tournoi des vi nations', 'uefa', 'vendee globe', 'watch live', 'world cup', 'xv de france', 'xv'];

// Filter out if there is no text summary
//if (!preg_match('//'.'[a-z]+'.'//imu', strip_tags($summary)) || $summary == null)

// 20 Minutes
$feedfilter['20minutes']['title'] = '20 Minutes';
$feedfilter['20minutes']['url']   = [
    //'https://www.20minutes.fr/feeds/rss-une.xml',
    'https://www.20minutes.fr/feeds/rss-actu-france.xml',
    'https://www.20minutes.fr/feeds/rss-france.xml',
];
$feedfilter['20minutes']['blacklist']['category']['contains'] = ['bd', 'by the web', 'cinema', 'comics', 'culture', 'fake off', 'horoscope quotidien chinois', 'horoscope quotidien', 'horoscope', 'instagram', 'media', 'medias', 'musique', 'people', 'serie', 'style', 't\'as vu ?', 'television', 'video'];
$feedfilter['20minutes']['blacklist']['content']['contains']  = ['« 20 Minutes »'];
$feedfilter['20minutes']['blacklist']['title']['contains']    = ['actu web du jour', 'dans le retro', 'en direct', 'en images', 'heure du bim', 'immanquables du jour', 'infographie', 'infos immanquables', 'interview', 'le récap', 'les infos du', 'mon bulletin dans ton urne', 'toute l\'info'];
$feedfilter['20minutes']['blacklist']['title']['starts']      = ['audio', 'people', 'quiz', 'video'];

// Apartment Therapy
$feedfilter['apartmenttherapy']['title'] = 'Apartment Therapy';
$feedfilter['apartmenttherapy']['url']   = [
    'https://www.apartmenttherapy.com/main.rss',
];

// Digg
$feedfilter['digg']['title'] = 'Digg';
$feedfilter['digg']['url']   = [
    'https://digg.com/rss/top.rss',
    'https://digg.com/rss/index.xml',
];

// Deals
$feedfilter['deals']['title'] = 'Deals';
$feedfilter['deals']['url']   = [
    'https://www.engadget.com/rss.xml',
    'https://gizmodo.com/rss',
    'https://lifehacker.com/rss',
];
$feedfilter['deals']['whitelist']['title']['contains'] = ['deal of the day', 'deal', 'deals', 'lowest price ever', 'off right now', 'on sale', 'prime day'];

// Engadget
$feedfilter['engadget']['title'] = 'Engadget';
$feedfilter['engadget']['url']   = [
    'https://www.engadget.com/rss.xml',
];
$feedfilter['engadget']['blacklist']['category']['contains'] = ['shopping', 'video games'];
$feedfilter['engadget']['blacklist']['title']['contains']    = ['\$\d*', 'all-time low', 'cheaper than ever', 'deals', 'drops to a new low of', 'hitting the books', 'off right now', 'on sale', 'prime day', 'recommended reading', 'the morning after'];

// Fatherly
$feedfilter['fatherly']['title'] = 'Fatherly';
$feedfilter['fatherly']['url']   = [
    'https://www.fatherly.com/rss',
];

// France 24
$feedfilter['france24']['title'] = 'France 24';
$feedfilter['france24']['url']   = [
    'https://www.france24.com/fr/ameriques/rss',
    'https://www.france24.com/fr/europe/rss',
    'https://www.france24.com/fr/france/rss',
    'https://www.france24.com/fr/monde/rss',
    'https://www.france24.com/fr/economie/rss',
    'https://www.france24.com/fr/culture/rss',
    'https://www.france24.com/fr/sciences/rss',
];
$feedfilter['france24']['blacklist']['category']['contains'] = ['a l\'affiche', 'la question qui fache', 'le paris des arts', 'legendes urbaines', 'les invites du jour', 'premieres'];
$feedfilter['france24']['blacklist']['content']['contains']  = ['(19|20)[0-9]{2}[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])']; // This regex filters out date with the format yyyy-mm-dd
$feedfilter['france24']['blacklist']['title']['contains']    = ['actu en dessin', 'en direct', 'invite du jour', 'reportage france 24'];

// France TV
$feedfilter['francetv']['title'] = 'France TV';
$feedfilter['francetv']['url']   = [
    'https://www.francetvinfo.fr/titres.rss',
    //'https://www.francetvinfo.fr/france.rss',
    //'https://www.francetvinfo.fr/monde.rss',
    //'https://www.francetvinfo.fr/economie.rss',
    //'https://www.francetvinfo.fr/decouverte.rss',
];
$feedfilter['francetv']['blacklist']['category']['contains'] = ['culture'];
$feedfilter['francetv']['blacklist']['title']['contains']    = ['c quoi l\ínfo', 'c\'est comment ailleurs', 'c\'est dans ma tete', 'ce qu\'il faut retenir', 'ces chansons qui font l\'actu', 'en direct', 'en images', 'info franceinfo', 'le brief politique', 'le monde de marie', 'le vrai du fake', 'le vrai du faux', 'les 4 verites', 'tout est politique', 'votre tele et vous'];
$feedfilter['francetv']['blacklist']['title']['starts']      = ['blog', 'carte', 'cinema', 'direct', 'document', 'eurozapping', 'feuilleton', 'infographie', 'infographies', 'la photo', 'la video.', 'musique', 'qizz', 'quiz', 'recit', 'video'];

// Geekwire
$feedfilter['geekwire']['title'] = 'Geekwire';
$feedfilter['geekwire']['url']   = [
    'https://www.geekwire.com/feed/',
];
$feedfilter['geekwire']['blacklist']['category']['contains'] = ['geekwire weekly', 'tech moves'];
$feedfilter['geekwire']['blacklist']['title']['contains']    = ['commentary', 'geek of the week', 'geekwire', 'geekwork picks', 'review', 'startup spotlight', 'tech moves', 'tldr', 'today on geekwire', 'video review', 'video\:', 'watch\:', 'week in geek', 'week in review', 'working geek'];

// GO Media
$feedfilter['gomedia']['title'] = 'Gizmondo & Lifehacker';
$feedfilter['gomedia']['url']   = [
    'https://gizmodo.com/rss',
    'https://lifehacker.com/rss',
];
$feedfilter['gomedia']['blacklist']['category']['contains'] = ['adult swim', 'adult swim', 'animated', 'animation', 'anime', 'books', 'cooking', 'deals', 'fitness', 'io9', 'plants', 'shopping'];
$feedfilter['gomedia']['blacklist']['content']['contains']  = ['io9'];
$feedfilter['gomedia']['blacklist']['title']['contains']    = ['\$\d*', '% off', 'best deals', 'deal of the day', 'io9', 'lowest price ever', 'off right now', 'on sale', 'prime day'];
$feedfilter['gomedia']['blacklist']['title']['contains']    = ['hitting the books', 'monday puzzle', 'the out-of-touch', 'this week', 'updates from', 'what people are getting wrong'];

// Huffington Post FR
$feedfilter['huffingtonpost_fr']['title'] = 'Huffington Post FR';
$feedfilter['huffingtonpost_fr']['url']   = [
    'https://www.huffingtonpost.fr/rss/all_headline.xml',
];

// Huffington Post US
$feedfilter['huffingtonpost_us']['title'] = 'Huffington Post US';
$feedfilter['huffingtonpost_us']['url']   = [
    'https://chaski.huffpost.com/us/auto/vertical/us-news',
];

// King News
$feedfilter['king']['title'] = 'King News';
$feedfilter['king']['url']   = [
    'http://rssfeeds.king5.com/king5/home',
    'http://rssfeeds.king5.com/king5/local',
];
$feedfilter['king']['blacklist']['category']['contains'] = ['en-espanol'];
$feedfilter['king']['blacklist']['title']['contains']    = ['5 things to know', 'best bargain', 'deal alert', 'tech deal', 'tech deals'];

// LCI
$feedfilter['lci']['title'] = 'La Chaine Info';
$feedfilter['lci']['url']   = [
    'https://www.lci.fr/feeds/rss-une.xml',
];

// Le Monde
$feedfilter['lemonde']['title'] = 'Le Monde';
$feedfilter['lemonde']['url']   = [
    'https://www.lemonde.fr/rss/une.xml',
    'https://www.lemonde.fr/etats-unis/rss_full.xml',
    'https://www.lemonde.fr/planete/rss_full.xml',
    'https://www.lemonde.fr/sciences/rss_full.xml',
];

// Makeuseof
$feedfilter['makeuseof']['title'] = 'Makeuseof';
$feedfilter['makeuseof']['url']   = [
    'https://www.makeuseof.com/feed/',
];

// My Northwest
$feedfilter['mynorthwest']['title'] = 'My Northwest';
$feedfilter['mynorthwest']['url']   = [
    'https://www.mynorthwest.com/category/local/feed/',
];
$feedfilter['mynorthwest']['blacklist']['category']['contains'] = ['opinion'];
$feedfilter['mynorthwest']['blacklist']['title']['contains']    = ['am newsdesk', 'ap photos', 'crime blotter', 'crime corner', 'kiro newsradio headlines', 'pm newsdesk', 'rantz'];

// Politico
$feedfilter['politico']['title'] = 'Politico';
$feedfilter['politico']['url']   = [
    'https://www.politico.eu/feed/',
];

// Shoreline
$feedfilter['shoreline']['title'] = 'Shoreline';
$feedfilter['shoreline']['url']   = [
    'https://www.shorelineareanews.com/feeds/posts/default?alt=rss',
];
$feedfilter['shoreline']['blacklist']['category']['contains'] = ['birds', 'book review', 'cartoon', 'city council lfp', 'flowers', 'insects', 'obituaries', 'people', 'poem', 'poetry', 'sunset', 'theater', 'travels with charlie', 'veterans', 'wildlife'];

// The Guardian
$feedfilter['theguardian']['title'] = 'The Guardian';
$feedfilter['theguardian']['url']   = [
    'https://www.theguardian.com/us/rss',
    'https://www.theguardian.com/us-news/rss',
    'https://www.theguardian.com/world/rss',
    'https://www.theguardian.com/us/business/rss',
    'https://www.theguardian.com/us/technology/rss',
    'https://www.theguardian.com/science/rss',
];

// The Seattle Times
$feedfilter['theseattletimes']['title'] = 'The Seattle Times';
$feedfilter['theseattletimes']['url']   = [
    'https://www.seattletimes.com/seattle-news/feed/',
    //'https://www.seattletimes.com/nation-world/feed/',
    //'https://www.seattletimes.com/business/feed/',
];

// The Stranger
$feedfilter['thestranger']['title'] = 'The Stranger';
$feedfilter['thestranger']['url']   = [
    'https://www.thestranger.com/seattle/Rss.xml',
];

// Time
$feedfilter['time']['title'] = 'Time';
$feedfilter['time']['url']   = [
    'https://time.com/feed/',
];
