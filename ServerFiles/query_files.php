<?php

include "session_tools.php";

$sid = create_session();

$username="cil070";
$endpoint="query_files";
$args='sid='.$sid.'&uid='.$username;

$time_start=microtime(true);

$data=curl_connect($endpoint, $args);
$decoded_data=json_decode($data);

if($decoded_data[0] == "Status: OK" && $decoded_data[2] == "Action: Continue"){
	
	$msg_data=explode(":", $decoded_data[1]);
	$file_data=json_decode($msg_data[1]);
	
	foreach ($file_data as $path){
		
		$extract_doc=explode('/', $path);
		$document=end($extract_doc);
		
		$extract_ext=explode('.', $document);
		$doc_name=$extract_ext[0];
		$ext=$extract_ext[1];
		
		$doc_fields=explode('-', $doc_name);
		$loan_num=$doc_fields[0];
		$doc_type=$doc_fields[1];
		$doc_date=$doc_fields[2];
		
		date_default_timezone_set('America/Chicago');
		$curr_date=date('m-d-Y H:i:s T');
		
		$db_link=db_connect('documents');
		
		$sql='INSERT INTO `queried_files` (`file_path`,`file_name`,`loan_num`,`file_type`,`file_date`,`extension`,`date_uploaded`, `data_recieved`) VALUES ("'.$path.'","'.$doc_name.'","'.$loan_num.'","'.$doc_type.'","'.$doc_date.'","'.$ext.'","'.$curr_date.'", 0)';
		
		$db_link->query($sql) or
			log_error('query_files.php', 'SQL', $db_link->error);
		
		//Check LN Existance
		$sql='SELECT `auto_id` FROM `loan_nums` WHERE `loan_num` LIKE "'.$loan_num.'"';
		$rsp=$db_link->query($sql) or
			log_error('query_files.php', 'SQL', $db_link->error);
		
		//If not, add new loan number
		if($rsp->num_rows == 0){
			$sql='INSERT INTO `loan_nums` (`loan_num`) VALUES ("'.$loan_num.'")';
			$db_link->query($sql) or
				log_error('query_files.php', 'SQL', $db_link->error);
		}
		
		//Check Type Existance
		$sql='SELECT `auto_id` FROM `file_type` WHERE `type` LIKE "'.$doc_type.'"';
		$rsp=$db_link->query($sql) or
			log_error('query_files.php', 'SQL', $db_link->error);
		
		//If not, add new file type
		if($rsp->num_rows == 0){
			$sql='INSERT INTO `file_type` (`type`) VALUES ("'.$doc_type.'")';
			$db_link->query($sql) or
				log_error('query_files.php', 'SQL', $db_link->error);
		}
		
		
	}
	
	log_files('Queried', sizeof($file_data));
	log_api_response($endpoint, $data, get_exe_time($time_start));
	close_session($sid);
	
}
else if($decoded_data[0] == "Status: OK" && $decoded_data[2] == "Action: None"){
	
	log_files('Queried', 0);
	log_api_response($endpoint, $data, get_exe_time($time_start));
	close_session($sid);
	
}
else{
	
	log_api_response($endpoint, $data, get_exe_time($time_start));
	close_session($sid);
	log_error('query_files.php', 'cURL', $decoded_data[1]);
	
}

function log_files($action, $num_items){
	
	$db_link=db_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `payload_log` (`action`, `num_items`, `date_logged`) VALUES ("'.$action.'", '.$num_items.', "'.$curr_date.'")';
	
	$db_link->query($sql) or
		log_error('query_files.php', 'SQL', $db_link->error);
	
	
}

function log_error($script, $type, $msg){
	
	$db_link=db_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `error_log` (`script`, `type`, `msg`, `date`) VALUES ("'.$script.'", "'.$type.'", "'.$msg.'", "'.$curr_date.'")';
	
	$db_link->query($sql);
	
	die();
	
}

function log_api_response($endpoint, $response, $exe_time){
	
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
	
	$db_link=db_connect('reporting');
	
	$db_link->query($sql) or
		log_error('query_files.php', 'SQL', $db_link->error);
	
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

