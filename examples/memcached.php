<?php

use Directus\ClosureCache\Cache;

// Skip this step; load via composer.
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Directus\\ClosureCache\\', __DIR__ . '/../src/');

// Initialize Cache interface
$Cache = new Cache(array(
    'adapter' => 'memcached',
    'options' => array(
        'servers' => array(
            array('127.0.0.1', 11211)
        )
    )
));

// Run the cached operations
require "slowOperations.php";