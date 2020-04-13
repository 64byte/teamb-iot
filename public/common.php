<?php
# include the composer autoloader
include __DIR__ .'/../vendor/autoload.php';

// define the base directory of the whole project
define('BASE_DIR', realpath(__DIR__.'/../'));

// application environment, such as development, production
define('APP_ENV_PRODUCTION', 'production');
define('APP_ENV_DEVELOPMENT', 'development');

define('JWT_KEY', bin2hex(openssl_random_pseudo_bytes(32)));

// set the default encoding
mb_internal_encoding("UTF-8");

// set the default timezone
date_default_timezone_set("PRC");
