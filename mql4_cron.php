<?php
session_start();
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

// Mql4Messages class
require_once BASE_PATH . '/lib/Mql4Messages.php';
$Mql4Messages = new Mql4Messages();

// If filter types are not selected we show latest added data first
$filter_col = 'timestamp';
$order_by = 'Desc';

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();
$select = array('id',
    'user_id', 
    'account', 
    'name', 
    'server', 
    'vps_id',
    'equity',
    'profit',
    'currency',
    'open_trades',
    'margin_level',
    'stopout_call',
    'stopout_stopout',
    'stopout_type',    
    'free_margin',
    'balance', 
    'timestamp', 
    'friendly_name', 
    'account_type',
    'ignore_account',
    'last_notification',
);

//Start building query according to input parameters.

//If order by option selected
$db->orderBy($filter_col, $order_by);

$db->where('updated_at',date("Y-m-d H:i:s",time()-60*15),'<');
$db->where("ignore_account","0","=");
$db->where("last_notification",time()-(60*60*1),"<"); // 1 hour

// Set pagination limit
$db->pageLimit = 100;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('mql4message', 1, $select);

$data_rows = $demo_rows = $offline_rows = array();
$ignore_count = $demo_count = $terminal_count = $vps_count = $offline_count = 0;

echo count($rows);
// organise by VPS
if( !empty($rows) ){
    foreach ($rows as $row) {
        if( $row['ignore_account'] == 1) continue;
        if( $row['account_type'] == 'demo' ) $demo_count++;
        if( is_check_day() ) {   // only offline during weekdays
            $offline_rows[$row['user_id']][$row['vps_id']][] = $row;
            $data_rows[] = $row['id'];
        }

        $terminal_count++;
    }
    unset($rows);
}
print_r($offline_rows); // offline terminals organised by user_id => vps_id
//print_r($data_rows);    // list of id's to update with last_notification

if( !empty($offline_rows) ){
    foreach ($offline_rows as $user_id => $vps) {
        $db->where('id',$user_id);
        $email = $db->getOne('admin_accounts','email');
        if( !empty($email) ){
            // got user email, continue
            // store notified ids of terminals for last_notification update

        }
    }

}


die();

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">MT4 Terminals</h1>
        </div>
        <!-- <div class="col-lg-6">
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
                Server Time: <span class="badge badge-info" style="padding: 8px;"><?= date('Y-m-d H:i:s'); ?></span>
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
                <th>Friendly Name</th>
                <th>Account</th>
                <th>Name</th>
                <th>Server</th>
                <th>VPS</th>
                <th>Balance</th>
                <th>Currency</th>
                <th>Open Trades</th>
                <th>Margin (Stopout)</th>
                <th>Last Ping</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
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
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['server']); ?></td>
                <td><?php echo htmlspecialchars($row['vps_id']); ?></td>
                <td style="background-color: <?=$dd_color;?>">
                    <span class="badge badge-primary">Balance</span> <?php echo number_format($current_balance,2); ?><br/>
                    <span class="badge badge-info">Profit</span> <?php echo number_format($current_profit,2); ?><br/>
                    <span class="badge badge-dark">Equity</span> <?php echo number_format($current_equity,2); ?> 

                    <?php
                        $dd = ($current_balance-$current_equity)/$current_balance*100;
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
            <?php endforeach;?>
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
