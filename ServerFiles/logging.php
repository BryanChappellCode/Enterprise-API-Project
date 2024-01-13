<?php

function log_api_interaction($endpoint, $response, $exe_time){
	
	$rsp=json_decode($response, true);
	
	$tmp=explode(':', $rsp[0]);
	$status=$tmp[1];
	
	$tmp=explode(':', $rsp[1]);
	$msg = (count($tmp) > 1) ? $tmp[1] : $tmp[0];
	$msg_size=strlen($msg);
	
	$tmp=explode(':', $rsp[2]);
	$action = (count($tmp) > 1) ? $tmp[1] : $tmp[0];
	
	date_default_timezone_set('America/Chicago');
	$date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `api_log` (`endpoint`,`status`,`msg_size`,`action`,`exe_time`,`date_logged`) VALUES ("'.$endpoint.'","'.$status.'",'.$msg_size.',"'.$action.'",'.$exe_time.',"'.$date.'")';
	
	$db_link=db_log_connect('reporting');
	
	$db_link->query($sql) or
		die("Something went wrong with $sql".$db_link->error);
	
}

function log_session($sid, $action){
	
	date_default_timezone_set('America/Chicago');
	$date=date('m-d-Y H:i:s T');
	
	if($action == "Create"){

		$sql='INSERT INTO `session_log` (`sid`, `date_opened`,`date_closed`) VALUES ("'.$sid.'","'.$date.'","")';

		$db_link=db_log_connect('reporting');

		$db_link->query($sql) or
			die("Something went wrong with $sql".$db_link->error);
		
	}
	else if($action == "Close"){
		
		$sql='UPDATE `session_log` SET `date_closed` = "'.$date.'" WHERE `sid` LIKE "'.$sid.'"';
		
		$db_link=db_log_connect('reporting');

		$db_link->query($sql) or
			die("Something went wrong with $sql".$db_link->error);
		
	}
}

function log_file_error($file, $error){
	
	$db_link=db_log_connect('reporting');
	
	$sql='INSERT INTO `file_error` (`err_file`,`err_msg`) VALUES ("'.$file.'","'.$error.'")';
	
	$db_link->query($sql) or
		die("Something went wrong with $sql".$db_link->error);
	
}

function db_log_connect($db_name){
	
	$un="WebUser";
	$pw="vI_qMtY[LoNw68jo";
	$db=$db_name;
	$hostname="localhost";
	$dblink=new mysqli($hostname,$un,$pw,$db);
	return $dblink;
	
}

?>