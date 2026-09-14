<?php

if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}

global $app;

$app->on('routing_started', function () {
    global $handler;

    if (!isset($_SESSION['uid'])) {
        return;
    }

    if ($handler === 'license') {
        return;
    }

    if (!class_exists('License')) {
        return;
    }

    $domain = $_SERVER['HTTP_HOST'] ?? parse_url(APP_URL, PHP_URL_HOST);

    if (!License::enforce($domain)) {
        r2(U . 'license/locked/');
    }
});
