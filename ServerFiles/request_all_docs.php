<?php

include("session_tools.php");

$sid = create_session();
$username="cil070";
$endpoint="request_all_documents";
$args='sid='.$sid.'&uid='.$username;

$time_start=microtime(true);
$data=curl_connect($endpoint, $args);
$decoded_data=json_decode($data,true);
$exe_time=get_exe_time($time_start);

$cnt=0;

if($decoded_data[0] == "Status: OK"){
	
	
	$msg=explode(":", $decoded_data[1]);
	$file_names=explode(',',$msg[1]);
	$arrlen=count($file_names);
	
	$file_names[0]=substr($file_names[0], 2);
	$file_names[$arrlen - 1]=trim($file_names[$arrlen - 1], ']');
	
	foreach ($file_names as $file){
		
		$trimmed_file=trim($file, '"');
		
		$extract_ext=explode('.', $trimmed_file);
		$doc_name=$extract_ext[0];
		$ext=$extract_ext[1];
		
		$db_link=db_connect('documents');
		
		$sql='SELECT `auto_id` FROM `queried_files` WHERE `file_name` LIKE "'.$doc_name.'"';
		
		$result=$db_link->query($sql) or
			log_error('request_all_docs.php', 'SQL', $db_link->error);
		
		//If file already exists in table
		if($result->num_rows != 0){
			continue;
		}
			
		$doc_fields=explode('-', $doc_name);
		$loan_num=$doc_fields[0];
		$doc_type=$doc_fields[1];
		$doc_date=$doc_fields[2];
		
		$date_arr=explode('_', $doc_date);
		
		$ymd = intval($date_arr[0]);
		
		if($ymd < 20231101){
			continue;
		}
		
		$path='/storage/files/cil070/'.$trimmed_file;
	
		date_default_timezone_set('America/Chicago');
		$curr_date=date('m-d-Y H:i:s T');
		
		$sql='INSERT INTO `queried_files` (`file_path`,`file_name`,`loan_num`,`file_type`,`file_date`,`extension`,`date_uploaded`, `data_recieved`) VALUES ("'.$path.'","'.$doc_name.'","'.$loan_num.'","'.$doc_type.'","'.$doc_date.'","'.$ext.'","'.$curr_date.'", 0)';
		
		$db_link->query($sql) or
			log_error('request_all_docs.php', 'SQL', $db_link->error);
		
		//Check LN Existance
		$sql='SELECT `auto_id` FROM `loan_nums` WHERE `loan_num` LIKE "'.$loan_num.'"';
		$rsp=$db_link->query($sql) or
			log_error('request_all_docs.php', 'SQL', $db_link->error);
		
		//If not, add new loan number
		if($rsp->num_rows == 0){
			$sql='INSERT INTO `loan_nums` (`loan_num`) VALUES ("'.$loan_num.'")';
			$db_link->query($sql) or
				log_error('request_all_docs.php', 'SQL', $db_link->error);
		}
		
		//Check Type Existance
		$sql='SELECT `auto_id` FROM `file_type` WHERE `type` LIKE "'.$doc_type.'"';
		$rsp=$db_link->query($sql) or
			log_error('request_all_docs.php', 'SQL', $db_link->error);
		
		//If not, add new file type
		if($rsp->num_rows == 0){
			$sql='INSERT INTO `file_type` (`type`) VALUES ("'.$doc_type.'")';
			$db_link->query($sql) or
				log_error('request_all_docs.php', 'SQL', $db_link->error);
		}
		
		$cnt++;
		
	}
	
	log_files('Queried', $cnt);
	log_api_response($endpoint, $data, $exe_time);
	close_session($sid);
}
else{
	
	log_api_response($endpoint, $data, $exe_time);
	close_session($sid);
	log_error('request_all_docs.php', 'cURL', $decoded_data[1]);
}

function log_files($action, $num_items){
	
	$db_link=db_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `payload_log` (`action`, `num_items`, `date_logged`) VALUES ("'.$action.'", '.$num_items.', "'.$curr_date.'")';
	
	$db_link->query($sql) or
		log_error('request_all_docs.php', 'SQL', $db_link->error);
	
	
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
		log_error('request_all_docs.php', 'SQL', $db_link->error);
	
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

