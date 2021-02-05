<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Mql4Messages class
require_once BASE_PATH . '/lib/Mql4Messages.php';
$Mql4Messages = new Mql4Messages();

// Get Input data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');

// Per page limit for pagination.
$pagelimit = 50;

// Get current page.
$page = filter_input(INPUT_GET, 'page');
if (!$page) {
	$page = 1;
}

// If filter types are not selected we show latest added data first
if (!$filter_col) {
	$filter_col = 'timestamp';
}
if (!$order_by) {
	$order_by = 'Desc';
}

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();

// $servers = $db->setQueryOption('DISTINCT')->where('user_id',$_SESSION['user_id'])->orderBy("timestamp","Desc")->get("mql4message",1000,"vps_id, timestamp");
// print_r($servers);

$select = array(
    'id',
    'user_id', 
    'account', 
    'name', // user/client account name with broker
    'server', 
    'vps_id',
    'equity',
    'profit',
    'currency',
    'leverage',
    'open_trades',
    'margin_level',
    'free_margin',
    'stopout_call',
    'stopout_stopout',
    'stopout_type',
    'balance', 
    'timestamp', 
    'ping', 
    'friendly_name', 
    'account_type',
    'start_balance_day',
    'start_balance_week',
    'start_balance_month',
    'start_balance_3month',
    'start_balance_year',
    'ignore_account',
    'trade_permitted',

);

$db->setTrace (true);

// select by user
$db->where('user_id',$_SESSION['user_id']);

//Start building query according to input parameters.
// If search string
if ($search_string) {
	$db->where('account', '%' . $search_string . '%', 'like');
    $db->orwhere('server', '%' . $search_string . '%', 'like');
    $db->orwhere('vps_id', '%' . $search_string . '%', 'like');
    $db->orwhere('friendly_name', '%' . $search_string . '%', 'like');
    $db->orwhere('currency', '%' . $search_string . '%', 'like');
}

if( isset($_GET['demo']) ){
    $db->where('account_type', 'demo', '!=');
}

//If order by option selected
if ($order_by) {
	$db->orderBy($filter_col, $order_by);
}

// Set pagination limit
$db->pageLimit = $pagelimit;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('mql4message', $page, $select);
$total_pages = $db->totalPages;

$user_accounts_raw = $db->getValue("mql4message", "name", null);

$rows2 = $demo_accounts = $accounts = $offline_rows = array();
$offline_count = 0;

if(!empty($rows)){
    foreach ($rows as $row){
        if( isset($_GET['account']) && !empty($_GET['account'])){
            if($row['name'] != $_GET['account']) continue;
        }
        
        if( is_check_day() && strtotime($row['ping']) < time()-(60*15) ){
            $online_status = false;
        } else {
            $online_status = true;
        }

        if( isset($_GET['online_status'])){
            if( $_GET['online_status'] == "offline" && $online_status ) continue;
            if( $_GET['online_status'] == "online" && !$online_status ) continue;
        }

        $row['equity_factor_sort'] = $row['balance'] > 0 ? ($row['profit']/$row['balance'])*10000 : 0;
        if( $row['account_type'] == 'demo' ){
            if( isset($demo_accounts[$row['account'].$row['server']]) && 
                strtotime($demo_accounts[$row['account'].$row['server']]['timestamp']) < strtotime($row['timestamp']) ) 
                $demo_accounts[$row['account'].$row['server']] = $row;
            elseif( !isset($demo_accounts[$row['account'].$row['server']]))
                $demo_accounts[$row['account'].$row['server']] = $row;
            
            continue;
        }

        if( !isset($rows2[$row['account'].$row['server']]) ) $rows2[$row['account'].$row['server']] = $row;

        if( strtotime($rows2[$row['account'].$row['server']]['timestamp']) < strtotime($row['timestamp']) ) $rows2[$row['account'].$row['server']] = $row;

        $rows2[$row['account'].$row['server']]['vps'][] = $row['vps_id'];



    }
    uasort($rows2, function($a, $b) {
        //return $a['balance'] - $b['balance'];
        return $a['equity_factor_sort'] - $b['equity_factor_sort'];
    });
    $rows2 = array_merge($rows2,$demo_accounts);

    $accounts = array_unique($user_accounts_raw);
    sort($accounts, SORT_STRING | SORT_NATURAL);

}


//print_r ($db->trace);
//print_r($rows2);

$balance = 0;
$equity = 0;
$profit = 0;
$trades = 0;
$d_balance = 0;
$d_equity = 0;
$d_profit = 0;
$d_trades = 0;


//die();

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Trading Accounts</h1>
        </div>
        <!-- div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_customer.php?operation=create" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> Add new</a>
            </div>
        </div> -->
    </div>
    <?php include BASE_PATH . '/includes/flash_messages.php';?>

    <!-- Filters -->
    <div class="well bs-component filter-form">
        <button data-toggle="collapse" data-target="#filters" class="btn btn-primary">Show filters</button>
        <form class="form form-horizontal collapse" id="filters" action="">
            <div class="form-group">
                <label for="input_search" class="col-lg-2 control-label">Search</label>
                <div class="col-lg-10">
                  <input type="text" class="form-control" id="input_search" name="search_string" value="<?php echo htmlspecialchars($search_string, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="input_order" class="col-lg-2 control-label">Order By</label>
                <div class="col-lg-10">
                    <select name="filter_col" class="form-control">
                        <?php
                            foreach ($Mql4Messages->setOrderingValues() as $opt_value => $opt_name):
                            ($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
                            echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
                            endforeach;
                        ?>
                    </select>                
                </div>
            </div>
            <div class="form-group">
                <label for="input_order" class="col-lg-2 control-label">Filter by Account</label>
                <div class="col-lg-10">
                    <select name="account" class="form-control" id="input_order">
                        <option value="">--</option>
                        <?php if(!empty($accounts)) : foreach( $accounts as $account ) : ?>
                            <option value="<?php echo $account; ?>" <?php
                                if (isset($_GET['account']) && $_GET['account'] == $account) {
                                    echo 'selected';
                                }
                                ?> ><?php echo $account; ?>
                                
                            </option>    
                        <?php endforeach; endif; ?>
                    </select>            

                </div>
            </div>
            <div class="form-group">          
                <label for="demo" class="col-lg-2 control-label">Show only Real Accounts</label>
                <div class="col-lg-10">
                    <input type="checkbox" class="checkbox" aria-label="demo" name="demo" <?php if(isset($_GET['demo'])) echo "checked='checked'"; ?>>            
                </div>
            </div>
            <div class="form-group">          
                <label for="demo" class="col-lg-2 control-label">Do not show ignored</label>
                <div class="col-lg-10">
                    <input type="checkbox" aria-label="ignored" name="ignored" <?php if(isset($_GET['ignored'])) echo "checked='checked'"; ?>>          
                </div>
            </div> 
            <div class="form-group">          
                <label for="demo" class="col-lg-2 control-label">Show only</label>
                <div class="col-lg-10">
                    <div class="radio">
                        <label>
                          <input type="radio" name="online_status" id="online_status" value="all" <?php if((isset($_GET['online_status']) && $_GET['online_status'] == "all") || !isset($_GET['online_status']))  echo "checked='checked'"; ?>>
                          All
                        </label>
                    </div>                    
                    <div class="radio">
                        <label>
                          <input type="radio" name="online_status" id="online_status" value="offline" <?php if(isset($_GET['online_status']) && $_GET['online_status'] == "offline") echo "checked='checked'"; ?>>
                          Offline
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                          <input type="radio" name="online_status" id="online_status" value="online" <?php if(isset($_GET['online_status']) && $_GET['online_status'] == "online") echo "checked='checked'"; ?>>
                          Online
                        </label>
                    </div>

                </div>
            </div> 
            <input type="submit" value="Search" class="btn btn-primary">            
        </form>
          
    </div>
    <hr>
    <form class="form form-inline" action="" method="GET">
        <div class="clearfix">
            <?php 
                $autoupdate = isset($_GET['autoupdate']) && $_GET['autoupdate'] == 1 ? true : false;
            ?>
            <input type="hidden" name="autoupdate" value="<?php echo ($autoupdate)?"0":"1"; ?>" />
            <?php 
                $query = array();
                if( isset($_GET['search_string'] ) ) $query["search_string"] = $_GET['search_string'];
                if( isset($_GET['filter_col']    ) ) $query["filter_col"] = $_GET['filter_col'];
                if( isset($_GET['order_by']      ) ) $query["order_by"] = $_GET['order_by'];
                if( isset($_GET['demo']          ) ) $query["demo"] = $_GET['demo'];
                if( isset($_GET['online_status'] ) ) $query["online_status"] = $_GET['online_status'];
                if( !empty($query) ){
                    foreach ($query as $key => $value) {
                        echo "<input type='hidden' name='{$key}' value='{$value}'/>";
                    }
                } 
                
            ?>
            <div class="pull-left float-left">
                <button type="submit" class="btn btn-<?php echo ($autoupdate) ? "danger" : "success";  ?>" >
                    Auto-update 
                        <span class="glyphicon glyphicon-<?php echo $autoupdate ? "remove" : "refresh"; ?>"></span>
                </button>
                <input type='number' value="<?= isset($_GET['interval'])?$_GET['interval']:60;?>" name="interval" class="form-control" style="width:5em;" max="300" min="1" /> <span class="small">seconds</span>
                 
            </div>
            <div class="pull-right float-right text-right">
                <strong>Server Time:</strong> <span class="badge badge-success"><?= date('Y-m-d H:i'); ?></span><br/>
                New York: <span class="badge badge-primary"><?= date('Y-m-d H:i',strtotime('UTC -5 hours')); ?></span><br/>
                London: <span class="badge badge-warning"><?= date('Y-m-d H:i',strtotime('UTC')); ?></span><br/>
                Hong Kong: <span class="badge badge-info"><?= date('Y-m-d H:i',strtotime('UTC +8 hours')); ?></span><br/>            
            </div>
            <?php
                if( $autoupdate ){
                    echo "<script>setTimeout(function(){location.reload();}, ".(isset($_GET['interval'])?$_GET['interval']*1000:60000).");</script>";
                }
            ?>
        </div>
    </form>  
    <br/>

    <!-- //Filters -->

    <!-- Table -->
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th>Account</th>
                <th>Balance</th>
                <th>Info</th>
                <th>P/L</th>
                <th>Last Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($rows2)): ?>
                <?php foreach ($rows2 as $row): ?>
                    <?php if($row['account_type'] == 'demo' )  $demo = true; else $demo = false; ?>
                    <?php

                        $ignore = $row['ignore_account'];
                        $dd_color = "none";
                        $badge = "success";
                        if( $row['equity'] < $row['balance']*0.9 ){
                            // $dd_color = "#FFCC00";
                            $badge = "warning";
                        }
                        if( $row['equity'] < $row['balance']*0.8 ){
                            $dd_color = "#FFCCCC";
                            $badge = "warning";
                        }
                        if( $row['equity'] < $row['balance']*0.7 ){
                            $dd_color = "#FF9999";
                            $badge = "danger";
                        }
                        $current_balance = (is_numeric($row['balance']))?htmlspecialchars($row['balance']):0;
                        $current_equity = (is_numeric($row['equity']))?htmlspecialchars($row['equity']):0;
                        $current_profit = (is_numeric($row['profit']))?htmlspecialchars($row['profit']):0;

                    ?>                    
                    <tr class="<?php
                        echo ($demo) ? "text-info" : "";
                        echo ($ignore==1) ? " text-warning": "";
                    ?>">    
                        <td>
                            <strong><?php echo $row['friendly_name']; ?> </strong> 
                            <span class="badge badge-<?php echo $row['account_type']=="real" ? "primary":($row['account_type']=="demo"?"secondary":"warning");?>"><?php echo ucfirst($row['account_type']); ?> Account</span>
                            <br/>
                            (<a href="/mql4messages.php?search_string=<?php echo htmlspecialchars($row['account']); ?>"><?php echo htmlspecialchars($row['account']); ?></a>, <?php echo htmlspecialchars($row['server']); ?>) <br/>
                            <span class="small">[<?php echo $row['name']; ?>]</span> <br/>
                            <span class="small"><a href="/mql4messages.php?search_string=<?php echo htmlspecialchars($row['account']); ?>">(VPS: <?php
                                if(!empty($row['vps'])){
                                    $_vps = array_unique($row['vps']);
                                    echo implode(", ", $_vps);
                                }
                            ?></a>)</span>
                        </td>
                        <td style="background-color: <?=$dd_color;?>">
                            <span class="badge badge-primary">Balance</span> <?php echo number_format($current_balance,2); ?><br/>
                            <span class="badge badge-info">Profit</span> <?php echo number_format($current_profit,2); ?><br/>
                            <span class="badge badge-dark">Equity</span> <?php echo number_format($current_equity,2); ?> 

                            <?php
                                $dd = $current_balance > 0 ? ($current_balance-$current_equity)/$current_balance*100 : 0;
                            ?>                        
                            <span class="badge badge-<?= $badge; ?>">
                                <?php echo number_format(($dd<100)?-$dd:$dd,1);  ?>%
                            </span><br/>
                            <span class="badge badge-<?= $badge; ?>">
                                Margin 
                            </span> <?php echo number_format(htmlspecialchars($row['margin_level']),2); ?> %<br/>
                            (Margin call <?php
                                echo htmlspecialchars($row['stopout_call']."/".$row['stopout_stopout']);
                                echo "&nbsp;".($row['stopout_type']=='percent')? "%":$row['stopout_type']; 
                            ?> stopout)
                        </td>
                        <td>
                            <?php echo $row['currency']; ?><br/>
                            1:<?php echo $row['leverage']; ?><br/>
                            <span class="badge badge-<?php echo $row['trade_permitted']==1 ? "success":"danger";?>">Trade <?php echo $row['trade_permitted']==1?"":"NOT"; ?> Permitted</span><br/>
                            <span class="badge badge-dark"><?php echo htmlspecialchars($row['open_trades']); ?></span> open trades<br/>

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
                        
                            <?php
                                $style = "";
                                $last_update = $row['ping'];
                                if( $ignore != 1 ){
                                    if(is_check_day()){
                                        if( strtotime($last_update) < time()-(60*5)){
                                            //$style = " style='background-color:#FFCC99;'";
                                            $style = " class='warning'";
                                        }

                                        if( strtotime($last_update) < time()-(60*15)){
                                            //$style = " style='background-color:#FF9999;'";
                                            $style = " class='danger'";
                                        }
                                    }
                                }
                            ?>
                        <td<?= $style; ?>>
                            <?php 
                                if(!empty($style)) {
                                    echo "<span class='badge badge-danger'>OFFLINE for ";
                                    echo (time()-strtotime($row['ping'])>24*60*60) ? floor(abs(time() - strtotime($row['ping'])) / 86400) . " days, " . date("H:i",time()-strtotime($row['ping'])) : date("H:i",time()-strtotime($row['ping']));
                                    echo " (Hr:min)</span><br/>"; 
                                } elseif(!is_check_day()){
                                    echo "<span class='badge badge-secondary'>Not checking today</span><br/>";
                                } else echo "<span class='badge badge-success'>Online</span><br/>";
                            ?>
                            Dashboard: <?php echo htmlspecialchars($row['ping']); ?><br/>
                            MT4 Server: <?php echo htmlspecialchars($row['timestamp']); ?>
                            <?php
                                $t = "";
                                if( $row['timestamp'] > $last_update ) $t = "+"; // mt4 is after server : +
                                else $t = ""; // mt4 is before server: -
                                echo "({$t}".round((strtotime($row['timestamp']) - strtotime($last_update) )/60/60)."hrs)";
                            ?>                            
                            <?php echo ($ignore==1) ? "<br/><span class=''>(-+- ignored -+- )</span>":""; ?>
                        </td>

                        <td>
                            <a href="edit_mt4.php?entry_id=<?php echo $row['id']; ?>&operation=edit&redirect=<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-primary"><i class="glyphicon glyphicon-edit"></i></a>
                            <a href="#" class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id']; ?>"><i class="glyphicon glyphicon-trash"></i></a>
                        </td>
                    </tr>
                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="confirm-delete-<?php echo $row['id']; ?>" role="dialog">
                        <div class="modal-dialog">
                            <form action="delete_mt4.php" method="POST">
                                <!-- Modal content -->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Confirm</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="del_id" id="del_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="redirect" id="redirect" value="<?php echo $_SERVER['PHP_SELF']; ?>">
                                        <p>Are you sure you want to delete this row?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-default pull-left">Yes</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- //Delete Confirmation Modal -->
                    <?php

                        if( $demo ) {
                            $d_balance += $row['balance'];
                            $d_equity += $row['equity'];
                            $d_profit += $row['profit'];
                            $d_trades += $row['open_trades'];
                        } else {
                            $balance += $row['balance'];
                            $equity += $row['equity'];
                            $profit += $row['profit'];
                            $trades += $row['open_trades'];
                        }


                    ?>
                <?php endforeach;?>
                <tr class="text-primary" >
                    <td><h1> TOTAL </h1></td>

                    <?php
                        $dd_color = "none";
                        $badge = "secondary";
                        if( $equity <= $balance*0.8 ){
                            // $dd_color = "#FFFF99";
                            $badge = "warning";
                        }
                        if( $equity <= $balance*0.7 ){
                            $dd_color = "#FFCCCC";
                            $badge = "warning";
                        }
                        if( $equity <= $balance*0.6 ){
                            $dd_color = "#FF9999";
                            $badge = "danger";
                        }                    
                    ?>
                    <td style="background-color: <?=$dd_color;?>">
                        REAL<br/>
                        <span class="badge badge-primary">Balance</span> 
                            <?php echo number_format($balance,2); ?><br/>
                        <span class="badge badge-info">Profit</span> <?php echo number_format($profit,2); ?><br/>
                        <span class="badge badge-dark">Equity</span> <?php echo number_format($equity,2); ?> 

                        <?php
                            $dd = ($balance-$equity)/$balance*100;
                        ?>                        
                        <span class="badge badge-<?= $badge; ?>">
                            <?php echo number_format(($dd<100)?-$dd:$dd,1);  ?>%
                        </span><br/>
                        <span class="badge badge-dark">
                            <?php echo $trades; ?>
                        </span> open trades
                    </td>
                    <?php
                        if( !empty($d_balance)){
                            $dd_color = "none";
                            $badge = "secondary";
                            if( $d_equity <= $d_balance*0.8 ){
                                // $dd_color = "#FFFF99";
                                $badge = "warning";
                            }
                            if( $d_equity <= $d_balance*0.7 ){
                                $dd_color = "#FFCCCC";
                                $badge = "warning";
                            }
                            if( $d_equity <= $d_balance*0.6 ){
                                $dd_color = "#FF9999";
                                $badge = "danger";
                            }                    
                        }
                    ?>
                    <td style="background-color: <?= (!empty($d_balance))?$dd_color:"none";?>">
                        <?php if( !empty($d_balance)) : ?>
                            DEMO<br/>
                            <span class="badge badge-primary">Balance</span> 
                                <?php echo number_format($d_balance,2); ?><br/>
                            <span class="badge badge-info">Profit</span> <?php echo number_format($d_profit,2); ?><br/>
                            <span class="badge badge-dark">Equity</span> <?php echo number_format($d_equity,2); ?> 

                            <?php
                                $dd = ($d_balance-$d_equity)/$d_balance*100;
                            ?>                        
                            <span class="badge badge-<?= $badge; ?>">
                                <?php echo number_format(($dd<100)?-$dd:$dd,1);  ?>%
                            </span><br/>
                            <span class="badge badge-dark">
                                <?php echo $d_trades; ?>
                            </span> open trades                           
                        <?php endif; ?>
                    </td>

                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
            <?php endif; ?>
        </table>
        <hr/>
        <!-- //Table -->
    <div>
        Status page: <?php
            $db->where('id',$_SESSION['user_id']);
            $secret = $db->getOne('admin_accounts','secret');
            
            // echo "<pre>";print_r($_SERVER);echo "</pre>";
            if( isset($secret['secret'])) {
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/mt4status.php?s=".$secret['secret'];
                echo "<a href='{$url}'>{$url}</a>";
            }
        ?>
    </div>        
    <!-- Pagination -->
    <div class="text-center">
    <?php echo paginationLinks($page, $total_pages, 'mt4servers.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php';?>
