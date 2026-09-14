<?php

/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

_auth();
$mdate = $routes['1'];
$mdate /= 1000;
$mdate = (int) $mdate;
$mdate = date('Y-m-d',$mdate);
$user = User::_info();


$all_data = has_access($user->roleid,'transactions','all_data');


$d = Transaction::where('date',$mdate)->orderBy('id','desc');

if(!$all_data)
{
    $d->where('aid',$user->id);
}

$d = $d->get();

$dr = Transaction::where('date',$mdate);

if(!$all_data)
{
    $dr->where('aid',$user->id);
}

$dr = $dr->sum('dr');

if($dr == ''){
    $dr = '0.00';
}

$cr = Transaction::where('date',$mdate);

if(!$all_data)
{
    $cr->where('aid',$user->id);
}

$cr = $cr->sum('cr');



if($cr == ''){
    $cr = '0.00';
}
?>
<div class="panel-body" style="margin-top: 10px;">
<h4><?php echo $_L['Total Income']; ?>: <?php echo $config['currency_code'] .' '. number_format($cr,2,$config['dec_point'],$config['thousands_sep']); ?></h4>
<h4><?php echo $_L['Total Expense']; ?>:  <?php echo $config['currency_code'] .' '. number_format($dr,2,$config['dec_point'],$config['thousands_sep']); ?></h4>

<hr>
    <div class="row">
        <div class="col">
            <h4><?php echo $_L['All Transactions at Date']; ?>:  <?php echo date( $config['df'], strtotime($mdate)); ?></h4>
        </div>
<!--        <div class="col text-end">-->
<!--            <a href="--><?php //echo BASE_URL; ?><!--transactions/list" class="btn btn-primary btn-sm">--><?php //echo $_L['Filter']; ?><!--</a>-->
<!--        </div>-->
    </div>

<hr>
<table class="table table-striped table-bordered table-responsive" id="clx_datatable">

    <thead>
    <tr>
        <th><?php echo $_L['Account']; ?></th>
        <th><?php echo $_L['Type']; ?></th>
        <th><?php echo $_L['Category']; ?></th>
        <th class="text-right"><?php echo $_L['Amount']; ?></th>
        <th><?php echo $_L['Payer']; ?></th>
        <th><?php echo $_L['Payee']; ?></th>
        <th><?php echo $_L['Staff']; ?></th>
        <th><?php echo $_L['Method']; ?></th>
        <th><?php echo $_L['Ref']; ?></th>
        <th><?php echo $_L['Description']; ?></th>
        <th><?php echo $_L['Item']; ?></th>
<!--        <th class="text-right">--><?php //echo $_L['Dr']; ?><!--</th>-->
<!--        <th class="text-right">--><?php //echo $_L['Cr']; ?><!--</th>-->
    </tr>
    </thead>

<?php

$items = Item::all()->keyBy('id')->toArray();
$staffs = User::all()->keyBy('id')->toArray();

   foreach($d as $ds){


       $cls = '';
       $item = '';
       $staff = '';
       if(($ds['bal']) < 0){
           $cls = 'class="text-red"';
       }

         if(!empty($items[$ds['item_id']])){
              $item = $items[$ds['item_id']]['name'] ?? '';
         }

            if(!empty($staffs[$ds['aid']])){
                $staff = $staffs[$ds['aid']]['fullname'] ?? '';
            }

       $cat = $ds['category'] == 'Uncategorized' ? $_L['Uncategorized'] : $ds['category'];

       echo ' <tr>

        <td>'.$ds['account'].'</td>
        <td>'.ib_lan_get_line($ds['type']).'</td>
        <td>'.$cat.'</td>
        <td class="text-right">'.$config['currency_code'].' '.number_format($ds['amount'],2,$config['dec_point'],$config['thousands_sep']).'</td>
        <td>'.$ds['payer'].'</td>
        <td>'.$ds['payee'].'</td>
        <td>'.$staff.'</td>
        <td>'.$ds['method'].'</td>
        <td>'.$ds['ref'].'</td>
        <td>'.$ds['description'].'</td>
        <td>'.$item.'</td>
       
        

    </tr>';
   }



?>

<!--    <tfoot>-->
<!--    <tr>-->
<!--        <th colspan="9" style="text-align:right">--><?php //echo $_L['Total']; ?><!--</th>-->
<!--        <th>--><?php //echo ib_money_format($dr,$config); ?><!-- </th>-->
<!--        <th colspan="2">--><?php //echo ib_money_format($cr,$config); ?><!-- </th>-->
<!---->
<!--    </tr>-->
<!--    </tfoot>-->
</table>
    </div>
