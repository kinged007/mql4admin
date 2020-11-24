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
$filter_col = 'timestamp';
$order_by = 'Desc';

//Get DB instance. i.e instance of MYSQLiDB Library
$db = getDbInstance();

$db->where('secret',$get['s']);
$user = $db->getOne('admin_accounts','id');

if( empty($user)) header("Location: /index.php");

$select = array('id',
    'account', 
    'vps_id',
    'equity',
    'open_trades',
    'margin_level',
    'balance', 
    'timestamp', 
    'friendly_name', 
    'account_type',
    'ignore_account',
    // 'last_notification',
);

//Start building query according to input parameters.

//If order by option selected
$db->orderBy($filter_col, $order_by);

$db->where('user_id',$user['id']);
//$db->where('timestamp',date("Y-m-d H:i:s",time()-60*15),'<');
$db->where("ignore_account",0);
$db->orwhere("ignore_account IS NULL");

// Set pagination limit
$db->pageLimit = 100;

// Get result of the query.
$rows = $db->arraybuilder()->paginate('mql4message', 1, $select);

$data = $notify = array();

//print_r($rows);

$ignore_count = $active_count = $inactive_count =0;

if( !empty($rows) ){
    foreach ($rows as $row) {
        if( $row['ignore_account'] == 1 ) {
            $ignore_count++;
            continue;
        }
        if( strtotime($row['timestamp']) < time()-(60*5)){
            $inactive_count++;
            $data[] = $row;
            continue;
        }    
    }

}

$active_count = count($rows) - $ignore_count;
//print_r($data);

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
        <div class="pull-left float-left">
            Total Terminals: <span class="badge badge-info" style="padding: 8px;"><?= $active_count; ?></span><br/>
            Terminals Offline: <span class="badge badge-danger" style="padding: 8px;"><?= $inactive_count; ?></span><br/>
            <!-- Ignored Terminals:<span class="badge badge-secondary" style="padding: 8px;"><?= $ignore_count; ?></span> -->
<?php
    //echo "ACTIVE = Ignore | Inactive : ".$active_count . " = " . $ignore_count . " | " . $inactive_count . "  ";
?>
             
        </div>
        <div class="pull-right float-right text-right">
            <strong>Server Time:</strong> <span class="badge badge-success"><?= date('Y-m-d H:i:s'); ?></span><br/>
            New York: <span class="badge badge-primary"><?= date('Y-m-d H:i:s',strtotime('UTC -5 hours')); ?></span><br/>
            London: <span class="badge badge-warning"><?= date('Y-m-d H:i:s',strtotime('UTC')); ?></span><br/>
            Hong Kong: <span class="badge badge-info"><?= date('Y-m-d H:i:s',strtotime('UTC +8 hours')); ?></span><br/>        </div>
    </div>    
    <br/>


    <?php if(!empty($data)) : ?>

        <h2>Inactive Terminals</h2>
        <!-- Table -->
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Trading Account</th>
                    <th>VPS</th>
                    <th>Equity/Margin Level (%)</th>
                    <th>Last Connection</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
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
                    ?>
                    <td><?php echo $row['friendly_name']; ?> (<?php echo htmlspecialchars($row['account']); ?>) <?php echo ($demo) ? "<br/><span class='small text-muted'>(demo)</span>":""; ?></td>
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
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <!-- //Table -->
        
    <?php endif; ?>

    <form class="form form-inline" action="" method="GET">

        <div class="clearfix">
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
        </div>
    </form>

</div>
<!-- //Main container -->
<?php include BASE_PATH . '/includes/footer.php';?>
