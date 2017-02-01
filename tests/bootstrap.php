<?php

define('BASE_PATH', __DIR__.DIRECTORY_SEPARATOR.'_ProjectFiles'.DIRECTORY_SEPARATOR);

require_once  BASE_PATH . '../../vendor/autoload.php';

use Kit\Core\System;
use Kit\Enums\SystemEnvironments;

System::$environment = SystemEnvironments::UNIT_TEST;

echo 'bootstrap loaded' . PHP_EOL;