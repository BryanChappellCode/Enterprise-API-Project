<html>
<head>
	<title>Document Management System - Reports</title>
	<!-- BOOTSTRAP STYLES-->
	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<!--CUSTOM MAIN STYLES-->
	<link href="assets/css/custom.css" rel="stylesheet">
	<!-- BOOTSTRAP SCRIPTS -->
	<script src="assets/js/bootstrap.js"></script>
</head>
<body>
<?php
	//header('Content-Type: application/pdf');
	echo '<div id="page-inner">';
	echo '<h1 align="center" class="page-head-line">DocStorage Reports</h1>';
	echo '<hr>';
	
	$stats = storage_summary();
	loan_details($stats);

	

	
//Calculates Overall Summary of Database and returns Statistics Assoc Array
function storage_summary(){
	
	$statistics = array();
	
	$dblink=db_connect('documents');
	
	//Get Total Number of Loans
	$sql='SELECT count(`auto_id`) FROM `loan_nums`';
	$result=$dblink->query($sql) or
			log_error('reports.php', 'SQL', $db_link->error);
	$rst=$result->fetch_array(MYSQLI_NUM);
	$num_loans=$rst[0];
	$statistics['num_loans'] = $num_loans;
	
	//Get Total Size of All Documents
	$sql='SELECT sum(length(`data`)) FROM `file_data`';
	$result=$dblink->query($sql) or
			log_error('reports.php', 'SQL', $db_link->error);
	$rst=$result->fetch_array(MYSQLI_NUM);
	$tot_size=$rst[0];
	$tot_size_str = round($tot_size / pow(2,20), 2).'MB';
	$statistics['total_size'] = $tot_size;
	
	//Get Total Number of Documents
	$sql='SELECT count(`auto_id`) FROM `files`';
	$result=$dblink->query($sql) or
			log_error('reports.php', 'SQL', $db_link->error);
	$rst=$result->fetch_array(MYSQLI_NUM);
	$tot_docs=$rst[0];
	$statistics['total_docs'] = $tot_docs;
	
	//Get Average Size per Document
	$size_ave = $tot_size / $tot_docs;
	$size_ave_str = round($size_ave / pow(2,20),2).'MB';
	$statistics['average_size'] = $size_ave;
	
	//Get Average Number of Documents
	$doc_ave = round($tot_docs / $num_loans, 2);
	$statistics['average_docs'] = $doc_ave;
	
	//Summary Table
	echo '<div><table class="table table-bordered"  style="margin: 5% 15% 5% 15%; width: 70%;">';
	echo '<thead>';
	echo '<th style="white-space: nowrap">Total Loans</th>';
	echo '<th style="white-space: nowrap">Total Size</th>';
	echo '<th style="white-space: nowrap">Total Documents</th>';
	echo '<th style="white-space: nowrap">Average Size</th>';
	echo '<th style="white-space: nowrap">Average Documents</th>';
	echo '</thead>';
	echo '<tr>';
	echo '<td style="width: 20%">'.$num_loans.'</td>';
	echo '<td style="width: 20%">'.$tot_size_str.'</td>';
	echo '<td style="width: 20%">'.$tot_docs.'</td>';
	echo '<td style="width: 20%">'.$size_ave_str.'</td>';
	echo '<td style="width: 20%">'.$doc_ave.'</td>';
	echo '</tr>';
	echo '</table></div>';
	
	return $statistics;
	
}
	
//Calculates and Prints details about each Loan, needs stats array as an argument
function loan_details($stats){
	
	$dblink=db_connect('documents');
	
	$sql = 'SELECT `auto_id` FROM `file_type`';
	$result=$dblink->query($sql) or
			log_error('reports.php', 'SQL', $db_link->error);
	$num_types = $result->num_rows;
	
	
	$sql = 'SELECT * FROM `loan_nums`';
	$result=$dblink->query($sql) or
			log_error('reports.php', 'SQL', $db_link->error);
	
	echo '<div><table class="table table-bordered"  style="margin: 5% 15% 5% 15%; width: 70%;">';
	echo '<thead>';
	echo '<th style="white-space: nowrap">Loan Number</th>';
	echo '<th style="white-space: nowrap">Documents Completed</th>';
	echo '<th style="white-space: nowrap">Compared to Average</th>';
	echo '<th style="white-space: nowrap">Size Average</th>';
	echo '<th style="white-space: nowrap">Compared to Average</th>';
	echo '</thead>';
	
	
	//For each loan number
	while($row=$result->fetch_array(MYSQLI_ASSOC)){
		
		$id = $row['auto_id'];
		$loan = $row['loan_num'];

		//Get Number of Documents Completed by Loan Number
		$sql = 'SELECT `file_data` FROM `files` WHERE `loan_num` = '.$id;
		$rst=$dblink->query($sql) or
			log_error('reports.php', 'SQL', $db_link->error);
		$num_docs = $rst->num_rows;
		
		//Compare number of documents to the average
		if($num_docs < $stats['average_docs']){
			$num_cmp_str = 'Below Average';
		}
		else{
			$num_cmp_str = 'Above Average';
		}
	
		//Get the size of documents with loan number
		if($num_docs == 0){
			$doc_size_ave = 0;
		}
		else{
			$size = 0;
			
			while($data_row=$rst->fetch_array(MYSQLI_ASSOC)){	
				
				$sql = 'SELECT `data` FROM `file_data` WHERE `auto_id` = '.$data_row['file_data'];
				$rsp=$dblink->query($sql) or
					log_error('reports.php', 'SQL', $db_link->error);
				$szarr=$rsp->fetch_array(MYSQLI_NUM);
				$size += strlen($szarr[0]);
				
			}
			
			$doc_size_ave = $size / $num_docs;

		}
		
		if($doc_size_ave < $stats['average_size']){
			$doc_size_str = 'Below Average';
		}
		else{
			$doc_size_str = 'Above Average';
		}
		
		$size_str = round($doc_size_ave / pow(2,20) ,2).'MB';
		
		echo '<tr>';
		echo '<td>'.$loan.'</td>';
		echo '<td>'.$num_docs.'</td>';
		echo '<td>'.$num_cmp_str.'</td>';
		echo '<td>'.$size_str.'</td>';
		echo '<td>'.$doc_size_str.'</td>';
		echo '</tr>';
		
	}
	
	echo '</table>';
	
}	

	
		
		
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
	
	
</body>
</html>