<?php

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