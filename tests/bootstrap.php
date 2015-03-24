<?php

define('TESTS', 'this is test environment');

define('BASE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'_ProjectFiles'.DIRECTORY_SEPARATOR);

require_once 'vendor/autoload.php';

echo 'bootstrap loaded'.PHP_EOL;