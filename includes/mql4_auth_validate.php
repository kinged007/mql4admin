<?php

// Authenticate with secret

require_once './config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = filter_input(INPUT_POST, 'username');
	$secret = filter_input(INPUT_POST, 'secret');

	if( !isset($_POST['username']) || !isset($_POST['secret'])){
		exit("POST Data inconsistent.");
	}

	//Get DB instance.
	$db = getDbInstance();

	$db->where("user_name", $username);

	$row = $db->get('admin_accounts');

	if ($db->count >= 1) {

		$user_id = $row[0]['id'];
		$stored_secret = $row[0]['secret'];

		if ( $stored_secret == $secret ) {

			//Authentication successfull


		} else {
		
			exit("Invalid user name or secret."); // secret

		}

		//exit("0");
	} else {
		exit("Invalid user name or secret"); // username
	}

} else {
	die('Method Not allowed');
}