<?php
if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}

$base_theme = 'default';
$ui->assign('languages', Localization::getLanguages());

$ui->assign('selected_navigation', 'invoices');
$ui->assign('_st', 'Invoices');
$ui->assign('_title', $config['CompanyName']);

$action = route(1, 'home');

Event::trigger('client', [$action]);

$data = request()->all();
