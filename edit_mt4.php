<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


// Sanitize if you want
$entry_id = filter_input(INPUT_GET, 'entry_id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation',FILTER_SANITIZE_STRING); 
$redirect = filter_input(INPUT_GET, 'redirect',FILTER_SANITIZE_STRING); 
($operation == 'edit') ? $edit = true : $edit = false;
 $db = getDbInstance();

//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Get  id form query string parameter.
    $entry_id = filter_input(INPUT_GET, 'entry_id', FILTER_SANITIZE_STRING);

    //Get input data
    $data_to_update = filter_input_array(INPUT_POST);
// $db->setTrace(true);
    $data_to_update['updated_at'] = date('Y-m-d H:i:s');

    unset($data_to_update['active_mon']); // TODO - allow checking of server on specific days instead of mon-sun

    $db = getDbInstance();

    $sub_data_to_update['ignore_account'] = isset($data_to_update['ignore_account']) ? 1 : 0;
    if(isset($data_to_update['ignore_account'])) unset($data_to_update['ignore_account']);
    $db->where('id',$entry_id);
    $db->update('mql4message',$sub_data_to_update);

    $db->where('user_id',$_SESSION['user_id']);
    $db->where('account',$data_to_update['account']);
    $db->where('server',$data_to_update['server']);

    $stat = $db->update('mql4message', $data_to_update);
//  print_r ($_REQUEST); print_r ($db->trace);die();
    if($stat)
    {
        $_SESSION['success'] = "MQL4 entry updated successfully!";
        //Redirect to the listing page,
        header('location: '.$redirect);
        //Important! Don't execute the rest put the exit/die. 
        exit();
    }
}


//If edit variable is set, we are performing the update operation.
if($edit)
{
    $db->where('id', $entry_id);
    //Get data to pre-populate the form.
    $mql4 = $db->getOne("mql4message");
}
?>


<?php
    include_once 'includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <h2 class="page-header">MQL4 Entry</h2>
    </div>
    <!-- Flash messages -->
    <?php
        include('./includes/flash_messages.php')
    ?>

    <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
        
        <?php
            //Include the common form for add and edit  
            require_once('./forms/mql4_form.php'); 
        ?>
    </form>
</div>




<?php include_once 'includes/footer.php'; ?>