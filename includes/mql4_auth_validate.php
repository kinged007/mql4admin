<?php

// Authenticate with secret

require_once './config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = filter_input(INPUT_POST, 'username');
	$secret = filter_input(INPUT_POST, 'secret');

	//echo password_verify('admin', '$2y$10$RnDwpen5c8.gtZLaxHEHDOKWY77t/20A4RRkWBsjlPuu7Wmy0HyBu'); exit;

	//Get DB instance.
	$db = getDbInstance();

	$db->where("user_name", $username);

	$row = $db->get('admin_accounts');

	if ($db->count >= 1) {

		$user_id = $row[0]['id'];
		$stored_secret = $row[0]['secret'];

		if ( md5($stored_secret) == $secret ) {

			//Authentication successfull


		} else {
		
			exit("Invalid user name or secret");

		}

		//exit("0");
	} else {
		exit("Invalid user name or secret");
	}

} else {
	die('Method Not allowed');
}