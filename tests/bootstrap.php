<?php

error_reporting( E_ALL | E_STRICT );
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

// Composer Autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Directus\\ClosureCache\\', __DIR__ . '/../src/');