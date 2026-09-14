<?php

/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

$ui->assign('_title', $_L['Store'].'- '. $config['CompanyName']);
$id = route(1);

$item = ORM::for_table('sys_items')->find($id);

if($item){

    $ui->assign('item',$item);
    $ui->assign('_title', $item->name);

    view('cart_item');
}
