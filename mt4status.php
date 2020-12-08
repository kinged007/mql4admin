<?php

if( !isset($_GET['s']) ) header('Location: /index.php');

session_start();
$get = filter_input_array(INPUT_GET);
$secret = $get['s'];
//session_start();
require_once 'config/config.php';
//require_once BASE_PATH . '/includes/auth_validate.php';

// Mql4Messages class
require_once BASE_PATH . '/lib/Mql4Messages.php';
$Mql4Messages = new Mql4Messages();

// If filter types are not selected we show latest added data first
$filter_col = 'vps_id';
$order_by = 'Desc';

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();

$db->where('secret',$get['s']);
$user = $db->getOne('admin_accounts','id');

// print_r($user);

if( empty($user)) header("Location: /index.php");

$select = array('id',
    'account', 
    'server', 
    'vps_id',
    'equity',
    'profit',
    'currency',
    'leverage',
    'open_trades',
    'margin_level',
    'balance', 
    'timestamp', 
    'friendly_name', 
    'account_type',
    'trade_permitted',
    'ignore_account',
    'ping',
    'start_balance_day',
    'start_balance_week',
    'start_balance_month',
    'start_balance_3month',
    'start_balance_year',
    // 'last_notification',
);

//Start building query according to input parameters.

//If order by option selected
$db->orderBy($filter_col, $order_by);

$db->where('user_id',$user['id']);
//$db->where('timestamp',date("Y-m-d H:i:s",time()-60*15),'<');
// $db->where("ignore_account",1,"!=");
// $db->where("ignore_account IS NULL");

// Set pagination limit
$db->pageLimit = 100;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('mql4message', 1, $select);

$inactive_accounts = $notify = $trading_accounts = $demo_accounts = array();

//print_r($rows);

$ignore_count = $active_count = $inactive_count =0;

if( !empty($rows) ){
    foreach ($rows as $row) {        
        if( $row['ignore_account'] == 1 ) {
            $ignore_count++;
            continue;
        }

        $row['equity_factor_sort'] = $row['balance'] > 0 ? ($row['profit']/$row['balance'])*10000 : 0;

        if( $row['account_type']=="demo" ) $demo_accounts[$row['account'].$row['server']] = $row;
        else $trading_accounts[$row['account'].$row['server']] = $row;

        if( date("N") < 6 && strtotime($row['ping']) < time()-(60*15)){
            $inactive_count++;
            $inactive_accounts[] = $row;
            continue;
        }    
    }

    uasort($trading_accounts, function($a, $b) {
        //return $a['balance'] - $b['balance'];
        return $a['equity_factor_sort'] - $b['equity_factor_sort'];
    });

    $trading_accounts = array_merge($trading_accounts, $demo_accounts);

}

$active_count = count($rows) - $ignore_count;
// print_r($trading_accounts);

// print_r($notify);


include BASE_PATH . '/includes/header.php';

?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">MT4 Terminal Status</h1>
        </div>
        <!-- <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_customer.php?operation=create" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> Add new</a>
            </div>
        </div> -->
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php';?>
    <hr>

    <div class="clearfix">

        <form class="form form-inline" action="" method="GET">

            <!-- <div class="clearfix"> -->
                <?php 
                    $autoupdate = isset($_GET['autoupdate']) && $_GET['autoupdate'] == 1 ? true : false;
                ?>
                <input type="hidden" name="autoupdate" value="<?php echo ($autoupdate)?"0":"1"; ?>" />
                <input type="hidden" name="s" value="<?php echo $_GET['s']; ?>" />
                <div class="pull-left float-left">
                    <button type="submit" class="btn btn-<?php echo ($autoupdate) ? "danger" : "success";  ?>" >
                        Auto-update 
                            <span class="glyphicon glyphicon-<?php echo $autoupdate ? "remove" : "refresh"; ?>"></span>
                    </button>
                    <input type='number' value="<?= isset($_GET['interval'])?$_GET['interval']:60;?>" name="interval" class="form-control" style="width:5em;" max="300" min="1" /> <span class="small">seconds</span>
                     
                </div>
                <?php
                    if( $autoupdate ){
                        echo "<script>setTimeout(function(){location.reload();}, ".(isset($_GET['interval'])?$_GET['interval']*1000:60000).");</script>";
                    }
                ?>            
            <!-- </div> -->
        </form>        

        <div class="pull-right float-right text-right">
            <strong>Server Time:</strong> <span class="badge badge-success"><?= date('Y-m-d H:i:s'); ?></span><br/>
            New York: <span class="badge badge-primary"><?= date('Y-m-d H:i:s',strtotime('UTC -5 hours')); ?></span><br/>
            London: <span class="badge badge-warning"><?= date('Y-m-d H:i:s',strtotime('UTC')); ?></span><br/>
            Hong Kong: <span class="badge badge-info"><?= date('Y-m-d H:i:s',strtotime('UTC +8 hours')); ?></span><br/>        </div>
    </div>    
    <br/>

    <div >
        <span class="badge badge-info" style="padding: 8px;"><?= $active_count; ?></span> Total Terminals, with <span class="badge badge-danger" style="padding: 8px;"><?= $inactive_count; ?></span> offline.
    <!-- Ignored Terminals:<span class="badge badge-secondary" style="padding: 8px;"><?= $ignore_count; ?></span> -->
<?php
//echo "ACTIVE = Ignore | Inactive : ".$active_count . " = " . $ignore_count . " | " . $inactive_count . "  ";
?>
     
</div>

    <?php if(!empty($inactive_accounts)) : ?>

        <hr/>
        <h2>Offline Terminals</h2>
        <!-- Table -->
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Trading Account</th>
                    <th>VPS</th>
                    <th>Equity/Margin Level (%)</th>
                    <th>Last Contact</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inactive_accounts as $row): ?>
                    <?php if($row['account_type'] == 'demo' )  $demo = true; else $demo = false; ?>
                    <?php
                        $dd_color = "none";
                        $badge = "success";
                        if( $row['equity'] < $row['balance']*0.8 ){
                            // $dd_color = "#FFCC00";
                            $badge = "warning";
                        }
                        if( $row['equity'] < $row['balance']*0.7 ){
                            $dd_color = "#FFCCCC";
                            $badge = "warning";
                        }
                        if( $row['equity'] < $row['balance']*0.6 ){
                            $dd_color = "#FF9999";
                            $badge = "danger";
                        }
                        $current_balance = (is_numeric($row['balance']))?htmlspecialchars($row['balance']):0;
                        $current_equity = (is_numeric($row['equity']))?htmlspecialchars($row['equity']):0;

                    ?>                    
                    <tr<?= ($demo) ? " style='background-color:#CCFFFA;font-style:italic;'" : ""; ?>>    
                        <td><?php echo $row['friendly_name']; ?> (<?php echo htmlspecialchars($row['account']); ?>) 
                        </td>
                        <td><?php echo htmlspecialchars($row['vps_id']); ?></td>
                        <td style="background-color: <?=$dd_color;?>">

                            <?php
                                $dd = $current_balance > 0 ? ($current_balance-$current_equity)/$current_balance*100 : 0;
                            ?>                        
                            <span>Equity
                                <span class="badge badge-<?= $badge; ?>">
                                    <?php echo number_format(($dd<100)?-$dd:$dd,1);  ?>%
                                </span><br/>
                            </span>
                            <span>Margin Level
                                <span class="badge badge-<?= $badge; ?>">
                                    <?php echo number_format($row['margin_level'],1);  ?>%
                                </span><br/>
                            </span>
                            <span>Open Trades
                                <span class="badge badge-info">
                                    <?php echo $row['open_trades'];  ?>
                                </span><br/>
                            </span>                    
                        </td>
                            <?php
                                $style = "";
                                $last_update = $row['ping'];
                                $ignore = $row['ignore_account'];
                                if( $ignore != 1 ){
                                    if( date("N") < 6 ){
                                        if( strtotime($last_update) < time()-(60*5)){
                                            $style = " style='background-color:#FFCC99;'";
                                        }

                                        if( strtotime($last_update) < time()-(60*15)){
                                            $style = " style='background-color:#FF9999;'";
                                        }
                                    }
                                }
                            ?>
                        <td<?= $style; ?>>
                            <?php 
                                if(!empty($style)) 
                                    echo "<span class='badge badge-danger'>Offline for ";
                                    echo (time()-strtotime($row['ping'])>24*60*60) ? floor(abs(time() - strtotime($row['ping'])) / 86400) . " days, " . date("H:i",time()-strtotime($row['ping'])) : date("H:i",time()-strtotime($row['ping']));
                                    echo " (Hr:min)</span><br/>"; 
                            ?>

                            Server: <?php echo htmlspecialchars($row['ping']); ?><br/>
                            MT4 Server: <?php echo htmlspecialchars($row['timestamp']); ?>
                            <?php echo ($ignore==1) ? "<br/><span class='small text-muted'>(ignored)</span>":""; ?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <!-- //Table -->
        
    <?php endif; ?>

    <?php if(!empty($trading_accounts)) : ?>
        <hr/>
        <h2>Trading Accounts</h2>
        <!-- Table -->
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Trading Account</th>
                    <th>Details</th>
                    <th>Equity/Margin Level (%)</th>
                    <th>P/L</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trading_accounts as $row): ?>
                    <?php if($row['account_type'] == 'demo' )  $demo = true; else $demo = false; ?>
                    <?php
                        $dd_color = "none";
                        $badge = "success";
                        if( $row['equity'] < $row['balance']*0.8 ){
                            // $dd_color = "#FFCC00";
                            $badge = "warning";
                        }
                        if( $row['equity'] < $row['balance']*0.7 ){
                            $dd_color = "#FFCCCC";
                            $badge = "warning";
                        }
                        if( $row['equity'] < $row['balance']*0.6 ){
                            $dd_color = "#FF9999";
                            $badge = "danger";
                        }
                        $current_balance = (is_numeric($row['balance']))?htmlspecialchars($row['balance']):0;
                        $current_equity = (is_numeric($row['equity']))?htmlspecialchars($row['equity']):0;

                    ?>                    
                    <tr<?= ($demo) ? " style='background-color:#CCFFFA;font-style:italic;'" : ""; ?>>    

                        <td><strong><?php echo $row['friendly_name']; ?></strong><br/>
                            (<?php echo htmlspecialchars($row['server']); ?>)
                        </td>
                        <td>
                            <?php echo $row['currency']; ?><br/>
                            1:<?php echo $row['leverage']; ?><br/>
                            <span class="badge badge-<?php echo $row['trade_permitted']==1 ? "success":"danger";?>">Trade <?php echo $row['trade_permitted']==1?"":"NOT"; ?> Permitted</span><br/>
                            <span class="badge badge-<?php echo $row['account_type']=="real" ? "primary":($row['account_type']=="demo"?"secondary":"warning");?>"><?php echo ucfirst($row['account_type']); ?> Account</span><br/>
                        </td>
                        <td style="background-color: <?=$dd_color;?>">

                            <?php
                                $dd = $current_balance > 0 ? ($current_balance-$current_equity)/$current_balance*100 : 0;
                            ?>                        
                            <span>Equity
                                <span class="badge badge-<?= $badge; ?>">
                                    <?php echo number_format(($dd<100)?-$dd:$dd,1);  ?>%
                                </span><br/>
                            </span>
                            <span>Margin Level
                                <span class="badge badge-<?= $badge; ?>">
                                    <?php echo number_format($row['margin_level'],1);  ?>%
                                </span><br/>
                            </span>
                            <span>Open Trades
                                <span class="badge badge-info">
                                    <?php echo $row['open_trades'];  ?>
                                </span><br/>
                            </span>                    
                        </td>
                        <td>
                            <?php
                                $start_balance_day = (is_numeric($row['start_balance_day']))?htmlspecialchars($row['start_balance_day']):0;
                                $start_balance_week = (is_numeric($row['start_balance_week']))?htmlspecialchars($row['start_balance_week']):0;
                                $start_balance_month = (is_numeric($row['start_balance_month']))?htmlspecialchars($row['start_balance_month']):0;
                                $start_balance_3month = (is_numeric($row['start_balance_3month']))?htmlspecialchars($row['start_balance_3month']):0;
                                $start_balance_year = (is_numeric($row['start_balance_year']))?htmlspecialchars($row['start_balance_year']):0;

                                if( $start_balance_day > 0 )
                                    echo "<span class='badge badge-dark'>Day</span> ".number_format(($current_balance-$start_balance_day)/$start_balance_day*100,2)."%<br/>"; 
                               if( $start_balance_week > 0 )
                                    echo "<span class='badge badge-secondary'>Week</span> ".number_format(($current_balance-$start_balance_week)/$start_balance_week*100,2)."%<br/>"; 
                               if( $start_balance_month > 0 )
                                    echo "<span class='badge badge-info'>Month</span> ".number_format(($current_balance-$start_balance_month)/$start_balance_month*100,2)."%<br/>";
                                if( $start_balance_3month > 0 )
                                    echo "<span class='badge badge-primary'>3Month</span> ".number_format(($current_balance-$start_balance_3month)/$start_balance_3month*100,2)."%<br/>";
                                if( $start_balance_year > 0 )
                                    echo "<span class='badge badge-success'>Year</span> ".number_format(($current_balance-$start_balance_year)/$start_balance_year*100,2)."%<br/>";                                                     
                            ?>
                        </td>                        
                    </tr>
                    <?php endforeach;?>
            </tbody>
        </table>
        <!-- //Table -->
        
    <?php endif; ?>
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php';?>
