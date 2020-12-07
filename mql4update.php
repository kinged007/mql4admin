<?php

require_once 'config/config.php';
require_once BASE_PATH . '/includes/mql4_auth_validate.php';

// Costumers class
require_once BASE_PATH . '/lib/Mql4Messages.php';
$Mql4Messages = new Mql4Messages();

/*
Array
(
    [username]   => admin
    [secret] => 370a9382a9bb3da0126fbbb6dd4b4759
    [balance] => 1025.25
    [credit] => 0
    [company] => International Capital Markets Pty Ltd
    [currency] => EUR
    [equity] => 600.00
    [margin] => 307
    [margin_level] => 4446.23
    [free_margin] => 578.12
    [leverage] => 500
    [name] => Barnard Joshua
    [account] => 20002412
    [profit] => -425.25
    [server] => ICMarkets-Live24
    [stopout_call] => 100
    [stopout_stopout] => 50
    [trade_permitted] => true
    [ea_permitted] => true
    [dll_allowed] => true
    [account_type] => real
    [stopout_type] => percent
    [vps_id] => Contabo-1152
    [timestamp] => 1605136371       // MT4 Server time
    [ping] => 1605136371            // Dashboard server time
)
*/

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($user_id) ) 
{

    //Get input data
    $data_to_update = filter_input_array(INPUT_POST); //array_filter($_POST); //filter_input_array(INPUT_POST);
    
    unset($data_to_update['username']);
    unset($data_to_update['secret']);

    $data_to_update['user_id'] = $user_id;

    //Check whether server already exists ;
    $db = getDbInstance();
    $db->where('account', $data_to_update['account']);
    $db->where('server', $data_to_update['server']);
    $db->where('vps_id', $data_to_update['vps_id']);
    $db->where('user_id', $user_id);
    //print_r($data_to_update['user_name']);die();
    $row = $db->getOne('mql4message');
    //print_r($data_to_update);
    //print_r($row); die();

// var_dump($data_to_update);

    $ts = strtotime($data_to_update['timestamp']); // mt4 server time
    $data_to_update['updated_at'] = date('Y-m-d H:i:s');
    $data_to_update['ping'] = date('Y-m-d H:i:s'); // dashboard server time
    // $data_to_update['timestamp'] = date('Y-m-d H:i:s',$data_to_update['timestamp']);

    // balance for day/week/month
    if( $ts > strtotime( 'monday this week', $ts ) && strtotime($row['timestamp']) < strtotime( 'monday this week', $ts ) ){
        $data_to_update['start_balance_week'] = $data_to_update['balance'];
    }
    if( $ts > strtotime( 'today', $ts ) && strtotime($row['timestamp']) < strtotime( 'today', $ts ) ){
        $data_to_update['start_balance_day'] = $data_to_update['balance'];
    }
    if( $ts > strtotime(date('Y-m-1'), $ts) && strtotime($row['timestamp']) < strtotime(date('Y-m-1', $ts)) ){
        $data_to_update['start_balance_month'] = $data_to_update['balance'];
    }
    if( $ts > strtotime(date('Y-m-1', $ts)." -3 months") && strtotime($row['timestamp']) < strtotime(date('Y-m-1', $ts)." -3 months") ){
        $data_to_update['start_balance_3month'] = $data_to_update['balance'];
    }
    if( $ts > strtotime(date('Y-1-1', $ts)) && strtotime($row['timestamp']) < strtotime(date('Y-1-1', $ts)) ){
        $data_to_update['start_balance_year'] = $data_to_update['balance'];
    }

    // Update
    if (!empty($row['account'])) {

        $data_to_update['updated_at'] = date('Y-m-d H:i:s');
        $db = getDbInstance();
//$db->setTrace (true);       
        $db->where('id',$row['id']);
        $stat = $db->update('mql4message', $data_to_update);
//print_r ($db->trace);
        if($stat)
        {
            //Important! Don't execute the rest put the exit/die. 
            exit("Update Successful");
        } else {
            exit('Update failed: ' . $db->getLastError());
        }

        exit;
    } else {
        // Create new
        //Insert timestamp
        $data_to_update['created_at'] = date('Y-m-d H:i:s');
        $db = getDbInstance();
        
        $last_id = $db->insert('mql4message', $data_to_update);

        if($last_id)
        {
            exit("Create Successful");
        }
        else
        {
            exit('Create failed: ' . $db->getLastError());
        }
    }

    die();

} else {
    exit("Method not allowed or Invalid user id");
}