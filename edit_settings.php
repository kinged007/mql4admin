<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


//User ID for which we are performing operation
$admin_user_id = $_SESSION['user_id']; // filter_input(INPUT_GET, 'admin_user_id');
$operation = filter_input(INPUT_GET, 'operation', FILTER_SANITIZE_STRING);
($operation == 'edit') ? $edit = true : $edit = false;

$edit = true;

//Serve POST request.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Sanitize input post if we want
	$data_to_update = filter_input_array(INPUT_POST);
	//Check whether the user name already exists ;
	$db = getDbInstance();
	$db->where('user_name', $data_to_update['user_name']);
	$db->where('id', $admin_user_id, '!=');
	//print_r($data_to_update['user_name']);die();
	$row = $db->getOne('admin_accounts');
	//print_r($data_to_update['user_name']);
	//print_r($row); die();

	if (!empty($row['user_name'])) {

		$_SESSION['failure'] = "User name already exists";

		$query_string = http_build_query(array(
			'admin_user_id' => $admin_user_id,
			'operation' => $operation,
		));
		header('location: edit_admin.php?'.$query_string );
		exit;
	}

	//$admin_user_id = filter_input(INPUT_GET, 'admin_user_id', FILTER_VALIDATE_INT);
	//Encrypting the password
	if( isset($data_to_update['generate_new_secret'])){
		$data_to_update['secret'] = getSecureRandomToken();
		unset($data_to_update['generate_new_secret']);
	}

	$db = getDbInstance();
	$db->where('id', $admin_user_id);
	$stat = $db->update('admin_accounts', $data_to_update);

	if ($stat) {
		$_SESSION['success'] = "Settings have been updated successfully";
	} else {
		$_SESSION['failure'] = "Failed to update Settings : " . $db->getLastError();
	}

	header('location: edit_settings.php');
	exit;

}

//Select where clause
$db = getDbInstance();
$db->where('id', $admin_user_id);

$admin_account = $db->getOne("admin_accounts");

// Set values to $row

// import header
require_once 'includes/header.php';
?>
<div id="page-wrapper">

    <div class="row">
     <div class="col-lg-12">
            <h2 class="page-header">Settings</h2>
        </div>

    </div>
    <?php include_once 'includes/flash_messages.php';?>
    <form class="well form-horizontal" action="" method="post"  id="contact_form" enctype="multipart/form-data">
        <?php include_once './forms/user_settings.php';?>
    </form>
</div>




<?php include_once 'includes/footer.php';?>