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
    'margin',
    'margin_level',
    'free_margin',
    'balance', 
    'timestamp', 
    'friendly_name', 
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

    if( !isset($rows2[$row['server']]) ) $rows2[$row['server']] = $row;

    if( strtotime($rows2[$row['server']]['timestamp']) < strtotime($row['timestamp']) ) $rows2[$row['server']] = $row;


}

//print_r ($db->trace);
//print_r($rows2);



//die();

include BASE_PATH . '/includes/header.php';
?>
<!-- Main container -->
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="page-header">Trading Accounts</h1>
        </div>
        <div class="col-lg-6">
            <div class="page-action-links text-right">
                <a href="add_customer.php?operation=create" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> Add new</a>
            </div>
        </div>
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
        <?php 
            $autoupdate = isset($_GET['autoupdate']) && $_GET['autoupdate'] == 1 ? true : false;
        ?>
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
        <input type="hidden" name="autoupdate" value="<?php echo ($autoupdate)?"0":"1"; ?>" />
        <button type="submit" class="btn btn-<?php echo ($autoupdate) ? "danger" : "success";  ?>" >
            Auto-update 
                <span class="glyphicon glyphicon-<?php echo $autoupdate ? "remove" : "refresh"; ?>"></span>
        </button>
        <?php
            if( $autoupdate ){
                echo "<script>setTimeout(function(){location.reload();}, 60000);</script>";
            }
        ?>
        </button>
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
                <th>Equity</th>
                <th>Profit</th>
                <th>Currency</th>
                <th>Margin</th>
                <th>Margin_level</th>
                <th>Free_margin</th>
                <th>Last Ping</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows2 as $row): ?>
            <tr>
                <?php
                    $dd_color = "none";
                    if( $row['equity'] <= $row['balance']*0.8 ){
                        $dd_color = "yellow";
                    }
                    if( $row['equity'] <= $row['balance']*0.7 ){
                        $dd_color = "orange";
                    }
                    if( $row['equity'] <= $row['balance']*0.6 ){
                        $dd_color = "red";
                    }
                ?>
                <td><?php echo $row['friendly_name']; ?></td>
                <td><?php echo htmlspecialchars($row['account']); ?></td>
                <td><?php echo htmlspecialchars($row['server']); ?></td>
                <td style="background-color: <?=$dd_color;?>"><?php echo htmlspecialchars($row['balance']); ?></td>
                <td style="background-color: <?=$dd_color;?>"><?php echo htmlspecialchars($row['equity']); ?></td>
                <td style="background-color: <?=$dd_color;?>"><?php echo htmlspecialchars($row['profit']); ?></td>
                <td><?php echo htmlspecialchars($row['currency']); ?></td>
                <td><?php echo htmlspecialchars($row['margin']); ?></td>
                <td><?php echo htmlspecialchars($row['margin_level']); ?></td>
                <td><?php echo htmlspecialchars($row['free_margin']); ?></td>
                
                    <?php
                        $last_update = $row['timestamp'];
                        $style = "";
                        if( strtotime($last_update) < time()-(60*15)){
                            $style = " style='background-color:red;'";
                        }
                    ?>
                <td<?= $style; ?>>

                    <?php echo htmlspecialchars($last_update); ?>
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
