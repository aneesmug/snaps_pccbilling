<?php

/*
|--------------------------------------------------------------------------
| Disable direct access
|--------------------------------------------------------------------------
|
*/

if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}

/*
|--------------------------------------------------------------------------
| Application Build Number to handle cache & updates
|--------------------------------------------------------------------------
|
*/
$spEntry = 'config';

define('APP_MODE', 'default');

/*
|--------------------------------------------------------------------------
| Load the config file to connect with database and handle url
|--------------------------------------------------------------------------
|
*/

$_configPath = APP_SYSTEM_PATH . '/' . $spEntry . '.php';

if (is_file($_configPath) && is_readable($_configPath) && filesize($_configPath) > 0) {
    require $_configPath;
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($host !== '') {
        header('Location: ' . $scheme . '://' . $host . '/install/');
    } else {
        header('Location: /install/');
    }

    exit();
}

/*
|--------------------------------------------------------------------------
| To load composer autoload
|--------------------------------------------------------------------------
|
*/

require APP_BASE_PATH.'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Start the Application
|--------------------------------------------------------------------------
|
*/

require APP_SYSTEM_PATH.'/helpers/bootstrap.php';
