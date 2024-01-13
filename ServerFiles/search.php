<html>
<head>
	<title>Document Management System - Upload</title>
	<!-- BOOTSTRAP STYLES-->
	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<!-- FONTAWESOME STYLES-->
	<link href="assets/css/font-awesome.css" rel="stylesheet">
	   <!--CUSTOM BASIC STYLES-->
	<link href="assets/css/basic.css" rel="stylesheet">
	<!--CUSTOM MAIN STYLES-->
	<link href="assets/css/custom.css" rel="stylesheet">
	<!-- PAGE LEVEL STYLES -->
	<link href="assets/css/bootstrap-fileupload.min.css" rel="stylesheet">
	<!-- PAGE LEVEL STYLES -->
	<link href="assets/css/prettyPhoto.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="assets/css/print.css" media="print">
	<!--[if lt IE 9]><script src="scripts/flashcanvas.js"></script><![endif]-->
	<!-- JQUERY SCRIPTS -->
	<script src="assets/js/jquery-1.10.2.js"></script>
	<!-- BOOTSTRAP SCRIPTS -->
	<script src="assets/js/bootstrap.js"></script>
	<!-- METISMENU SCRIPTS -->
	<script src="assets/js/jquery.metisMenu.js"></script>
	   <!-- CUSTOM SCRIPTS <script src="assets/js/custom.js"></script>-->
	<script src="assets/js/bootstrap-fileupload.js"></script>

	<script src="assets/js/jquery.prettyPhoto.js"></script>
	<script src="assets/js/galleryCustom.js"></script>
	<script>
		function addFocus(div){document.getElementById(div).classList.remove("default");}
		function removeFocus(div){document.getElementById(div).classList.add("default");}
	</script>
</head>
<body>
<?php
	
if(isset($_POST['submit'])){
	
	$loan=$_POST['loanNum'];
	
	if(strlen($loan) == 0){
		$loan = '%';
	}
	else if(strlen($_POST['loanNum']) != 8){
		echo '<h1>Invalid Number of Digits in Loan Num (Need 8 or 0)</h1>';
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('search.php', 'Loan', "Invalid Length");		
	}	
	else if (!(preg_match("/^\d+$/", $loan))) {
    	echo '<h1>Invalid Characters in Loan Num</h1>';
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('upload-new.php', 'Loan', "Invalid Characters:".$loan);
	} 

	echo '<h1>'.$loan.'</h1>';

}
else{
	
	echo '<div id="page-inner">';
	echo '<h1 align="center" class="page-head-line">Search for File in DocStorage</h1>';
	echo '<div class="panel-body">';
	echo '<form method="post" action="">';
	echo '<div class="form-group">';
	echo '<label for="loanNum" class="control-label">Loan Number</label>';
	echo '<br>';
	echo '<input type="text" name="loanNum">';
	echo '<label for="loanNum" class="control-label">(Leave Blank for All)</label>';
	echo '</div>';
	echo '<div class="form-group">';
	echo '<label for="docType" class="control-label">Document Type</label>';
	echo '<select class="form-control" name="docType" style="width: 150px;">';
	echo '<option value="All">All</option>';
	
	$dblink=db_connect('documents');

	$sql='SELECT `auto_id`, `type` FROM `file_type`';

	$result=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);

	while($row=$result->fetch_array(MYSQLI_ASSOC)){

		echo '<option value="'.$row['auto_id'].'">'.$row['type'].'</option>';

	}

	echo '</select></div>';
	
	echo '<div class="form-group">';
	echo '<table>';
	echo '<tr><td>Day</td><td>Month</td><td>Year</td></tr>';
	
	echo '<tr><td>';
	
	echo '<select class="form-control" name="docDay">';
	echo '<option value="All">All</option>';
	//ADD DAY FUNCTIONALITY
	echo '</select>';
	
	echo '</td><td>';
	
	echo '<select class="form-control" name="docMonth">';
	echo '<option value="All">All</option>';
	$i=1;
	while($i < 13){
		
		echo '<option value="'.$i.'">'.$i.'</option>';
		
		$i+=1;
	}
	echo '</select>';
	
	echo '</td><td>';
	
	echo '<select class="form-control" name="docYear">';
	echo '<option value="All">All</option>';
	$i=1900;
	while($i < 2030){
		
		echo '<option value="'.$i.'">'.$i.'</option>';
		
		$i+=1;
	}
	echo '</select>';
	
	echo '</td></tr>';
	
	echo '</table>';
	echo '</div>';
	
	echo '<hr>';
	echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
	echo '</form>';
	echo '</div>';
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