<?php
####
#Author: Jos lieben
#Site: http://www.lieben.nu
#Copyright: use as you wish, but please leave this header intact
####

$login_name = (isset($_POST['login_name'])) ? $_POST['login_name'] : "";

echo "<html><head>
<link href=\"fx.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\">
<link href=\"fx.css\" rel=\"stylesheet\" type=\"text/css\">
</head><body><div id=\"wrapper\">";

if ($login_name=="") {
echo "Enter your myfxbook account login and password:<br>
<form name=\"login\" method=\"POST\" action=\"\">
login: <input type=\"text\" name=\"login_name\" value=\"joshua@munyard.biz\"> password: <input type=\"password\" name=\"login_pw\">
<input type=\"submit\" value=\"show fxbook\"></form><br>";
}
else {

$login_string = "$login_name&password=$login_pw";
$url = "http://www.myfxbook.com/api/login.xml?email=$login_string";

$login = file_get_contents($url);
$xml_sess = simplexml_load_string($login);
$sess = $xml_sess->session;

$url="http://www.myfxbook.com/api/get-my-accounts.xml?session=$sess";
$data = file_get_contents($url);
$xml_data = simplexml_load_string($data);

$i =0;
echo "<table class=\"dataTable\" cellpadding=\"3\"><tr><td width=\"50px\" align=\"left\"><b>Name</b></td><td width=\"50px

\"><b>Profit</b></td><td width=\"55px\"><b>Profit Abs</b></td><td width=\"50px\"><b>Balance</b></td><td width=\"50px

\"><b>Equity</b></td><td><b>DD</b></td><td width=\"55px\"><b>Age</b></td><td><b>Deposits</b></td><td width=\"55px

\"><b>Updated:</b></td><td></td></tr>";
foreach ($xml_data->xpath('//account') as $niks) {

    echo "<tr><td align=\"left\"><b>";
    echo $xml_data->accounts->account[$i]->name;
    echo "</b></td><td>";
    $profit = (double) $xml_data->accounts->account[$i]->profit;
    $deposits = (double) $xml_data->accounts->account[$i]->deposits;
    $withdrawals = (double) $xml_data->accounts->account[$i]->withdrawals;
    $net_deposits = $deposits-$withdrawals;
    $equity = (double) $xml_data->accounts->account[$i]->equity;
    $balance = (double) $xml_data->accounts->account[$i]->balance;
    $drawdown = (double) $xml_data->accounts->account[$i]->drawdown;
    $per = ($profit/$net_deposits)*100;
    $aper = (($equity-$net_deposits)/$net_deposits)*100;
    $nu = time();
    $start = strtotime($xml_data->accounts->account[$i]->creationDate);
    $looptijd = floor(($nu-$start)/(60*60*24));
    $update = strtotime($xml_data->accounts->account[$i]->lastUpdateDate);
    $updated = floor(($nu-$update)/60);
    if ($per > 0) {
        echo "<font color=\"green\">+";
    }else{
        echo "<font color=\"red\">-";
    }
    print round($per,2);    
    echo "%</font></td><td>";
    if ($aper > $per) echo "<b>";
    if ($aper > 0) {
        $profit = 1;
        echo "<font color=\"green\">+";
    }else{
        $profit = 0;
        echo "<font color=\"red\">";
    }
    print round($aper,2);
    echo "%</font></td><td>";
    if ($aper > $per) echo "</b>";
    if($balance>$net_deposits){
        echo "<font color=\"green\">";
    }else{
        echo "<font color=\"red\">";
    }
    echo "€ ";
    echo round($balance,2);
    echo "</font></td><td>";
    if($equity>$net_deposits){
        echo "<font color=\"green\">";
    }else{
        echo "<font color=\"red\">";
    }
    echo "€ ";
    echo round($equity,2);
    echo "</font></td><td>";
    echo round($drawdown,2);
    echo "%</td>";

    echo "<td>$looptijd days</td><td>€ $net_deposits</td><td>";
    if($updated > 30) echo "<font color=\"red\"><b>";
    echo "$updated Min";
    if($updated > 30) echo "</b></font>";
    echo "</td><td>";
    echo "<img src=\"http://mobile.myfxbook.com/system-spark.png?id=";
    echo $xml_data->accounts->account[$i]->id;    
    echo "\"></td></tr>";
    $i++;

}
echo "</table>";


$url = "http://www.myfxbook.com/api/logout.xml?session=$sess";
$logout = file_get_contents($url);
$xml_logout = simplexml_load_string($logout);
}

echo "</div></body></html>";
    
                
?>