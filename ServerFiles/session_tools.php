<?php

function create_session(){
	$endpoint="create_session";
	$username="cil070";
	$password="GmWBkgRv!fr4JLw$";
	$args='username='.$username.'&password='.$password;

	$time_start = microtime(true);

	$data=curl_connect($endpoint, $args);
	$create_data = json_decode($data, true);
	$exe_time=get_exe_time($time_start);

	if($create_data[0] == "Status: OK" && $create_data[1] == "MSG: Session Created"){

		$sid = $create_data[2];

		log_session_api_response($endpoint, $data, $exe_time);
		log_session($sid, "Open");
		
		return $sid;
		
	}
	else{
		
		log_session_api_response($endpoint, $data, $exe_time);
		log_session_error('session_tools.php', 'Session', 'Error Creating Session');
		
	}
}



function close_session($sid){
	
	$endpoint="close_session";
	$username="cil070";
	$args='sid='.$sid.'&uid='.$username;
	
	$time_start=microtime(true);
	
	$data=curl_connect($endpoint, $args);
	$close_data=json_decode($data,true);
	$exe_time=get_exe_time($time_start);
	
	if($close_data[0] == "Status: OK"){
		
		log_session($sid, "Close");
		log_session_api_response($endpoint, $data, $exe_time);
	}
	else{
		log_session_api_response($endpoint, $data, $exe_time);
		clear_session();
		log_session_error('session_tools.php', 'Session', 'Error Closing Session');
	}
}

function clear_session(){

	$endpoint="clear_session";
	$username="cil070";
	$password="GmWBkgRv!fr4JLw$";
	$args='username='.$username.'&password='.$password;

	$time_start = microtime(true);

	$data=curl_connect($endpoint, $args);
	$clear_data=json_decode($data, true);
	$exe_time=get_exe_time($time_start);
	
	
	if($clear_data[0] == "Status: OK"){
		
		date_default_timezone_set('America/Chicago');
		$date=date('m-d-Y H:i:s T');
		
		$db_link=db_session_connect('reporting');
		
		$sql='UPDATE `session_log` SET `date_closed` = "'.$date.'" WHERE `date_closed` LIKE ""';
		
		$db_link->query($sql) or
			log_session_error('session_tools.php', 'SQL', $db_link->error);
		
		//API LOG
		log_session_api_response($endpoint, $data, $exe_time);
		
		
	}
	else{
		
		//API_LOG
		log_session_api_response($endpoint, $data, $exe_time);
		log_session_error('session_tools.php', 'Session', 'Error Clearing Session');
			
	}
	
}

function log_session($sid, $action){
	
	date_default_timezone_set('America/Chicago');
	$date=date('m-d-Y H:i:s T');
	
	if($action == "Open"){

		$sql='INSERT INTO `session_log` (`sid`, `date_opened`,`date_closed`) VALUES ("'.$sid.'","'.$date.'","")';

		$db_link=db_session_connect('reporting');

		$db_link->query($sql) or
			log_session_error('session_tools.php', 'SQL', $db_link->error);
		
	}
	else if($action == "Close"){
		
		$sql='UPDATE `session_log` SET `date_closed` = "'.$date.'" WHERE `sid` LIKE "'.$sid.'"';
		
		$db_link=db_session_connect('reporting');

		$db_link->query($sql) or
			log_session_error('session_tools.php', 'SQL', $db_link->error);
		
	}
}

function log_session_api_response($endpoint, $response, $exe_time){
	
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
	
	$db_link=db_session_connect('reporting');
	
	$db_link->query($sql) or 
		log_session_error('session_tools.php', 'SQL', $db_link->error);
	
}

function log_session_error($script, $type, $msg){
	
	$db_link=db_session_connect('reporting');
	
	date_default_timezone_set('America/Chicago');
	$curr_date=date('m-d-Y H:i:s T');
	
	$sql='INSERT INTO `error_log` (`script`, `type`, `msg`, `date`) VALUES ("'.$script.'", "'.$type.'", "'.$msg.'", "'.$curr_date.'")';
	
	$db_link->query($sql);
	
	die();
	
}

function curl_connect($endpoint, $args){
	
	$curl=curl_init('https://cs4743.professorvaladez.com/api/'.$endpoint);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded',
		'content-length: '.strlen($args))
	);
	$data=curl_exec($curl);
	curl_close($curl);
	
	return $data;
	
}

function get_exe_time($start){
	$end = microtime(true);
	return ($end - $start)/60;
}

function db_session_connect($db_name){
	
	$un="WebUser";
	$pw="vI_qMtY[LoNw68jo";
	$db=$db_name;
	$hostname="localhost";
	$dblink=new mysqli($hostname,$un,$pw,$db);
	return $dblink;
	
}

?>
