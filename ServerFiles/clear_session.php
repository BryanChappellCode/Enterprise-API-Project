<?php

include "session_tools.php";

$dblink=db_connect('reporting');

$sql='SELECT `auto_id` FROM `session_log` WHERE `date_closed` LIKE ""';

$result=$dblink->query($sql) or
	die("Something went wrong with $sql".$dblink->error);

if($result->num_rows != 0){
	clear_session();
}

function db_connect($db_name){
	
	$un="WebUser";
	$pw="vI_qMtY[LoNw68jo";
	$db=$db_name;
	$hostname="localhost";
	$dblink=new mysqli($hostname,$un,$pw,$db);
	return $dblink;
	
}

?>