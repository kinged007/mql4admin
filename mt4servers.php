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
$pagelimit = 15;

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
    'server', 
    'vps_id',
    'equity',
    'profit',
    'currency',
    'open_trades',
    'margin_level',
    'free_margin',
    'stopout_call',
    'stopout_stopout',
    'stopout_type',
    'balance', 
    'timestamp', 
    'friendly_name', 
    'account_type',
    'start_balance_day',
    'start_balance_week',
    'start_balance_month',
    'start_balance_3month',
    'start_balance_year',
    'ignore_account',

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

$rows2 = array();
foreach ($rows as $row){

    if( !isset($rows2[$row['account'].$row['server']]) ) $rows2[$row['account'].$row['server']] = $row;

    if( strtotime($rows2[$row['account'].$row['server']]['timestamp']) < strtotime($row['timestamp']) ) $rows2[$row['account'].$row['server']] = $row;


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
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for="input_search">Search</label>
            <input type="text" class="form-control" id="input_search" name="search_string" value="<?php echo htmlspecialchars($search_string, ENT_QUOTES, 'UTF-8'); ?>">
            <label for="input_order">Order By</label>
            <select name="filter_col" class="form-control">
                <?php
foreach ($Mql4Messages->setOrderingValues() as $opt_value => $opt_name):
	($order_by === $opt_value) ? $selected = 'selected' : $selected = '';
	echo ' <option value="' . $opt_value . '" ' . $selected . '>' . $opt_name . '</option>';
endforeach;
?>
            </select>
            <select name="order_by" class="form-control" id="input_order">
                <option value="Asc" <?php
if ($order_by == 'Asc') {
	echo 'selected';
}
?> >Asc</option>
                <option value="Desc" <?php
if ($order_by == 'Desc') {
	echo 'selected';
}
?>>Desc</option>
            </select>
            <label>Only REAL accounts: <input type="checkbox" name="demo" <?php if(isset($_GET['demo'])) echo "checked='checked'"; ?>/></label>
            <input type="submit" value="Go" class="btn btn-primary">            
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
                <strong>Server Time:</strong> <span class="badge badge-success"><?= date('Y-m-d H:i:s'); ?></span><br/>
                New York: <span class="badge badge-primary"><?= date('Y-m-d H:i:s',strtotime('UTC -5 hours')); ?></span><br/>
                London: <span class="badge badge-warning"><?= date('Y-m-d H:i:s',strtotime('UTC')); ?></span><br/>
                Hong Kong: <span class="badge badge-info"><?= date('Y-m-d H:i:s',strtotime('UTC +8 hours')); ?></span><br/>            </div>
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
                <th>Friendly Name</th>
                <th>Account</th>
                <th>Server</th>
                <th>Balance</th>
                <th>Currency</th>
                <th>Open Trades</th>
                <th>Margin (Stopout)</th>
                <th>P/L</th>
                <th>Last Ping</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows2 as $row): ?>
            <?php if($row['account_type'] == 'demo' )  $demo = true; else $demo = false; ?>
            <tr<?= ($demo) ? " style='background-color:#00FFFF;font-style:italic;'" : ""; ?>>
                <?php
                    $dd_color = "none";
                    $badge = "secondary";
                    if( $row['equity'] <= $row['balance']*0.8 ){
                        $dd_color = "#FFFF99";
                        $badge = "info";
                    }
                    if( $row['equity'] <= $row['balance']*0.7 ){
                        $dd_color = "#FF9999";
                        $badge = "warning";
                    }
                    if( $row['equity'] <= $row['balance']*0.6 ){
                        $dd_color = "#FF3333";
                        $badge = "danger";
                    }
                    $current_balance = (is_numeric($row['balance']))?htmlspecialchars($row['balance']):0;
                    $current_equity = (is_numeric($row['equity']))?htmlspecialchars($row['equity']):0;
                    $current_profit = (is_numeric($row['profit']))?htmlspecialchars($row['profit']):0;

                ?>
                <td><?php echo $row['friendly_name']; ?></td>
                <td><?php echo htmlspecialchars($row['account']); ?></td>
                <td><?php echo htmlspecialchars($row['server']); ?></td>
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
                </td>
                <td><?php echo htmlspecialchars($row['currency']); ?></td>
                <td><?php echo htmlspecialchars($row['open_trades']); ?></td>
                <td><?php echo number_format(htmlspecialchars($row['margin_level']),2); ?> %<br/>
                    (<?php
                        echo htmlspecialchars($row['stopout_call']."/".$row['stopout_stopout']);
                        echo "&nbsp;".($row['stopout_type']=='percent')? "%":$row['stopout_type']; 
                    ?>)
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
                        $last_update = $row['timestamp'];
                        $ignore = $row['ignore_account'];
                        if( $ignore != 1 ){
                            if( strtotime($last_update) < time()-(60*5)){
                                $style = " style='background-color:#FFCC99;'";
                            }

                            if( strtotime($last_update) < time()-(60*15)){
                                $style = " style='background-color:#FF3333;'";
                            }
                        }
                    ?>
                <td<?= $style; ?>>

                    <?php echo htmlspecialchars($last_update); ?>
                    <?php echo ($ignore==1) ? "<br/><span class='small text-muted'>(ignored)</span>":""; ?>
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
            <tr style="background-color: #ccc; font-weight: bold;">
                <td>TOTAL</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>

                <?php
                    $dd_color = "none";
                    $badge = "secondary";
                    if( $equity <= $balance*0.8 ){
                        $dd_color = "#FFFF99";
                        $badge = "info";
                    }
                    if( $equity <= $balance*0.7 ){
                        $dd_color = "#FF9999";
                        $badge = "warning";
                    }
                    if( $equity <= $balance*0.6 ){
                        $dd_color = "#FF3333";
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
                </td>
                <?php
                    if( !empty($d_balance)){
                        $dd_color = "none";
                        $badge = "secondary";
                        if( $d_equity <= $d_balance*0.8 ){
                            $dd_color = "#FFFF99";
                            $badge = "info";
                        }
                        if( $d_equity <= $d_balance*0.7 ){
                            $dd_color = "#FF9999";
                            $badge = "warning";
                        }
                        if( $d_equity <= $d_balance*0.6 ){
                            $dd_color = "#FF3333";
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
                    <?php endif; ?>
                </td>

                <td>
                    <?php
                        echo $trades;
                        echo (!empty($d_trades)) ? "<br/>DEMO: ".$d_trades : "";
                    ?>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <!-- //Table -->

    <!-- Pagination -->
    <div class="text-center">
    <?php echo paginationLinks($page, $total_pages, 'mql4update.php'); ?>
    </div>
    <!-- //Pagination -->
</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php';?>
