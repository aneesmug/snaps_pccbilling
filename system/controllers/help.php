<?php

/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

_auth();
$ui->assign('selected_navigation', 'accounts');
$ui->assign('_title', 'Accounts- ' . $config['CompanyName']);
$ui->assign('_st', 'Accounts');
$action = $routes['1'];
$user = authenticate_admin();
switch ($action) {
    case 'dashboard':
        view('help-dashboard');
        break;

    default:
        echo 'action not defined';
}
