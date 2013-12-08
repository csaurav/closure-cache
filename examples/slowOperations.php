<?php

if(basename(__FILE__) === current($argv)) {
	die("This is loaded via the example files. Don't run this file directly.\n");
}

/**
 * "Slow operation"
 */

function getRandomWikipediaArticleTitle() {
    $context = stream_context_create(
        array(
            'http' => array(
                'follow_location' => false
            )
        )
    );
    $html = file_get_contents("http://en.wikipedia.org/wiki/Special:Random", false, $context);
    $wikipediaUrl = false;
    foreach($http_response_header as $header) {
        if("Location:" === substr($header, 0, 9)) {
            $wikipediaUrl = substr($header, 10);
        }
    }
    $articleTitle = "Error";
    if($wikipediaUrl) {
        $articleSlugPos = strrpos($wikipediaUrl, '/');
        if($articleSlugPos) {
            $articleTitle = substr($wikipediaUrl, $articleSlugPos + 1);
            $articleTitle = str_replace('_', ' ', urldecode($articleTitle));
        }
    }
    return $articleTitle;
}

/**
 * Usage
 */

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