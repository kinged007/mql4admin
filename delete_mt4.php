<?php 
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';
$del_id = filter_input(INPUT_POST, 'del_id');
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') 
{

	if($_SESSION['admin_type']!='super'&&$_SESSION['admin_type']!='admin'){
		$_SESSION['failure'] = "You don't have permission to perform this action";
        header('location: '.(isset($_POST['redirect'])?$_POST['redirect']:'mql4messages.php'));
        exit;

	}
    $entry_id = $del_id;

    $db = getDbInstance();
    $db->where('id', $entry_id);
    $status = $db->delete('mql4message');
    
    if ($status) 
    {
        $_SESSION['info'] = "MT4 Entry deleted successfully!";
        header('location: '.(isset($_POST['redirect'])?$_POST['redirect']:'mql4messages.php'));
        exit;
    }
    else
    {
    	$_SESSION['failure'] = "Unable to delete MT4 Entry.";
        header('location: '.(isset($_POST['redirect'])?$_POST['redirect']:'mql4messages.php'));
        exit;

    }
    
}