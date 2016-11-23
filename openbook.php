<?php
require __DIR__ . '/vendor/autoload.php';

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(__DIR__);

$app = new OpenBook\Application();
return $app->run($argc, $argv);
