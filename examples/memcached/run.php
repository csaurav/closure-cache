<?php

use Directus\ClosureCache\Cache;

// Skip this step; load via composer.
$loader = require __DIR__ . '/../../vendor/autoload.php';
$loader->add('Directus\\ClosureCache\\', __DIR__ . '/../../src/');

// Initialize Cache interface
$Cache = new Cache(array(
	'adapter' => 'memcached',
	'options' => array(
		'servers' => array(
			array('127.0.0.1', 11211)
		)
	)
));

// This defines the random Wikipedia title fetcher:
require "../slowOperations.php";

echo "[On the fly]\n";

$articleTitle = $Cache->cache('randomWikipediaArticleTitle', function() {
	return getRandomWikipediaArticleTitle();
});

echo "Got Wikipedia article title: $articleTitle\n";

echo "\n";
echo "[Pre-defined]\n";

$Cache->define('anotherRandomWikipediaArticleTitle', function() {
	return getRandomWikipediaArticleTitle();
});

$articleTitle = $Cache->warm('anotherRandomWikipediaArticleTitle');

echo "Got Wikipedia article title: $articleTitle\n";

$Cache->expire('anotherRandomWikipediaArticleTitle');

echo "\n";
echo "(Expiring cache key.)\n";

$articleTitle = $Cache->warm('anotherRandomWikipediaArticleTitle');

echo "Got Wikipedia article title: $articleTitle\n";

echo "\n";
echo "[Pre-defined with arguments]\n";

$Cache->define('listArticleTitleMultipleTimes', function($times) use ($Cache) {
	$Cache->expire('anotherRandomWikipediaArticleTitle');
	$titleList = array();
	while($times--) {
		$titleList[] = $Cache->warm('anotherRandomWikipediaArticleTitle');
	}
	return $titleList;
});

echo "Listing random title 5 times:\n";
$titleList5 = $Cache->warm('listArticleTitleMultipleTimes', array(5));
foreach($titleList5 as $title) {
	echo "$title\n";
}

echo "\n";
echo "Listing random title 10 times:\n";
$titleList10 = $Cache->warm('listArticleTitleMultipleTimes', array(10));
foreach($titleList10 as $title) {
	echo "$title\n";
}

$Cache->expire('listArticleTitleMultipleTimes', array(5));
$Cache->expire('listArticleTitleMultipleTimes', array(10));