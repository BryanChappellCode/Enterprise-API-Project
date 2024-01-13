<?php

include "session_tools.php";

$endpoint='request_file';
$username='cil070';
$sid=create_session();

$db_link=db_connect('documents');

$sql="SELECT * FROM `queried_files` WHERE `data_recieved` = 0 LIMIT 250";

$data=$db_link->query($sql) or
	log_error('get_file_data.php', 'SQL', $db_link->error);

$cnt=0;

$start=microtime(true);

while($document=$data->fetch_array(MYSQLI_ASSOC)){
	
	$fid=$document['file_name'].'.'.$document['extension'];
	
	//If the response was not a pdf
	if($document['extension'] != "pdf"){
		log_error('get_file_data.php', 'File', 'Extension Error:'.$fid);
		continue;
	}

	$time_start=microtime(true);
	$args='sid='.$sid.'&uid='.$username.'&fid='.$fid.'';
	$response=curl_connect($endpoint, $args);
	$rsp_decoded=json_decode($response, true);
	$exe_time=get_exe_time($time_start);
	
	//If the file data was not recieved
	if(strlen($rsp_decoded) != 0){
		
		//Log Error
		log_error('get_file_data.php', 'File', 'File Data Not Reieved:'.$fid);
		continue;
	}
	
	$prep_rsp=addslashes($response);
	
	$file_size=strlen($response);
	
	//Get Loan Number ID
	$sql='SELECT `auto_id` FROM `loan_nums` WHERE `loan_num` LIKE "'.$document['loan_num'].'"';
	$rsp=$db_link->query($sql) or
		log_error('get_file_data.php', 'SQL', $db_link->error);
	$loan_arr=$rsp->fetch_array(MYSQLI_NUM);
	$loan_id=$loan_arr[0];
	
	//Get Type ID
	$sql='SELECT `auto_id` FROM `file_type` WHERE `type` LIKE "'.$document['file_type'].'"';
	$rsp=$db_link->query($sql) or
		log_error('get_file_data.php', 'SQL', $db_link->error);
	$type_arr=$rsp->fetch_array(MYSQLI_NUM);
	$type_id=$type_arr[0];
		$sql='INSERT INTO `file_data` (`file_name`,`data`) VALUES ("'.$fid.'","'.$prep_rsp.'")';
	
	$db_link->query($sql) or
		log_error('get_file_data.php', 'SQL', $db_link->error);
	
	$sql='SELECT `auto_id` FROM `file_data` WHERE `file_name` LIKE "'.$fid.'"';
	$rsp=$db_link->query($sql) or
		log_error('get_file_data.php', 'SQL', $db_link->error);
	$auto_id=$rsp->fetch_array(MYSQLI_ASSOC);
	$inc=$auto_id['auto_id'];
	
	$sql='INSERT INTO `files` (`file_name`, `file_data`, `loan_num`, `file_type`, `file_date`, `file_size`) VALUES ("'.$fid.'", "'.$inc.'", '.$loan_id.', '.$type_id.', "'.$document['file_date'].'", '.$file_size.')';
	
	$db_link->query($sql) or
		log_error('get_file_data.php', 'SQL', $db_link->error);
	
	$sql='UPDATE `queried_files` SET `data_recieved` = 1 WHERE `auto_id` = '.$document['auto_id'];
	$db_link->query($sql) or
		log_error('get_file_data.php', 'SQL', $db_link->error);
	
	$cnt++;
	
}

log_api_response($endpoint, "", get_exe_time($start));
log_files('Downloaded', $cnt);
close_session($sid);

function log_error($script, $type, $msg){
	
	$db_link=db_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `error_log` (`script`, `type`, `msg`, `date`) VALUES ("'.$script.'", "'.$type.'", "'.$msg.'", "'.$curr_date.'")';
	
	$db_link->query($sql);
	
	die();
	
}

function log_files($action, $num_items){
	
	$db_link=db_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `payload_log` (`action`, `num_items`, `date_logged`) VALUES ("'.$action.'", '.$num_items.', "'.$curr_date.'")';
	
	$db_link->query($sql) or
		die("Something went wrong with $sql".$db_link->error);
	
	
}

function log_api_response($endpoint, $response, $exe_time){
	
	$status="OK";
	$msg_size=0;
	$action="Downloaded";
	
	date_default_timezone_set('America/Chicago');
	$date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `api_log` (`endpoint`,`status`,`msg_size`,`action`,`exe_time`,`date_logged`) VALUES ("'.$endpoint.'","'.$status.'",'.$msg_size.',"'.$action.'",'.$exe_time.',"'.$date.'")';
	
	$db_link=db_connect('reporting');
	
	$db_link->query($sql) or
		die("Something went wrong with $sql".$db_link->error);
	
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