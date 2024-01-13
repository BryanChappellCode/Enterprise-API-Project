<?php


$dblink=db_connect('documents');

$fid=$_REQUEST['fid'];

$sql='SELECT `data` from `file_data` WHERE `auto_id` = '.$fid;

$result=$dblink->query($sql) or
	log_error('view_file.php', 'SQL', $dblink->error);

$data=$result->fetch_array(MYSQLI_NUM);
header('Content-Type: application/pdf');
header('Content-Length: '.strlen($data[0]));
echo $data[0];


function db_connect($db_name){

	$un="WebUser";
	$pw="vI_qMtY[LoNw68jo";
	$db=$db_name;
	$hostname="localhost";
	$dblink=new mysqli($hostname,$un,$pw,$db);
	return $dblink;

}

function log_error($script, $type, $msg){
	
	$db_link=db_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `error_log` (`script`, `type`, `msg`, `date`) VALUES ("'.$script.'", "'.$type.'", "'.$msg.'", "'.$curr_date.'")';
	
	$db_link->query($sql);
	
	die();
	
}


?>