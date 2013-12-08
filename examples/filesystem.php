<?php

use Directus\ClosureCache\Cache;

// Skip this step; load via composer.
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Directus\\ClosureCache\\', __DIR__ . '/../src/');

$cacheDir = './cache';

if(!file_exists($cacheDir) || !is_dir($cacheDir)) {
	if(!mkdir($cacheDir)) {
		die("Failed to create cache directory: $cacheDir.\n");
	}
}

// Initialize Cache interface
$Cache = new Cache(array(
    'adapter' => 'filesystem',
    'options' => array(
        'cache_dir' => $cacheDir,
        'dir_level' => 2
    )
));

// Run the cached operations
require "slowOperations.php";