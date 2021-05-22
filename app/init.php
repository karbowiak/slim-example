<?php

# Init composer
$autoloaderPath = dirname(__DIR__, 1) . '/vendor/autoload.php';
if (!file_exists($autoloaderPath)) {
    throw new RuntimeException('Error, autoload.php doesn\'t exist, please install dependencies with composer');
}

$autoloader = require(dirname(__DIR__, 1) . '/vendor/autoload.php');

return [new \App\Bootstrap($autoloader), $autoloader];