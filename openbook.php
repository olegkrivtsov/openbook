<?php
require __DIR__ . '/vendor/autoload.php';

$app = new OpenBook\Application();
return $app->run($argc, $argv);
