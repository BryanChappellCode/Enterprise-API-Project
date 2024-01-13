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
	
if(isset($_GET['loanNum'])){
	$_POST['loanNum'] = $_GET['loanNum'];
}
	
if(isset($_POST['submit'])){
	
	$loan=$_POST['loanNum'];
	
	//Check for Correct Length
	if(strlen($loan) != 8){
		echo '<h1>Invalid Number of Digits in Loan Num (Need 8)</h1>';
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('upload-existing.php', 'File', "Invalid Length");
	}
	
	//Check for Invalid Characters
	if (!(preg_match("/^\d+$/", $loan))) {
    	echo '<h1>Invalid Characters in Loan Num</h1>';
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('upload-existing.php', 'Loan', "Invalid Characters:".$loan);
	} 
	
	//Check if File was Uploaded
	if(strlen($_FILES['userFile']['name']) == 0){
		echo '<h1>No File Uploaded!</h1>';
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('upload-existing.php', 'File', "Empty File Upload");
	}
	
	//Check if File is a PDF
	if($_FILES['userFile']['type'] != 'application/pdf'){
		echo "<h1>Incorrect File Type (Must be PDF)</h1>";
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('upload-existing.php', 'File', "Invalid Extension");
	}
	
	//Get File Size
	$size=$_FILES['userFile']['size'];
	
	//Check if Size is too Big
	if($size > 10000000){
		echo "<h1>File too Big!</h1>";
		echo '<form method="post" action="">';
		echo '<button type="submit" name="refresh">Go Back</button>';
		echo '</form>';
		log_error('upload-existing.php', 'File', "File Too Big");
	}
	
	$dblink=db_connect('documents');
	
	//Check if Loan Number Exists
	$sql='SELECT `auto_id` FROM `loan_nums` WHERE `loan_num` LIKE "'.$loan.'"';
	$rst=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);
	//Redirect if exists
	if($rst->num_rows == 0){
		header('Location: /upload_new.php?loanNum='.$_POST['loanNum']);
		die();
	}
	
	$loan_arr=$rst->fetch_array(MYSQLI_NUM);
	$loan_id=$loan_arr[0];
	
	//Get Type ID
	$type_id=$_POST['docType'];
	$sql='SELECT `type` from `file_type` WHERE `auto_id` = '.$type_id.'';
	$rst=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);
	$rst_arr=$rst->fetch_array(MYSQLI_NUM);
	$type=$rst_arr[0];
	
	//Get Correct Date Format
	date_default_timezone_set('America/Chicago');
	$curr_date=date('Ymd_G_i_s');
	
	//Get Data from uploaded File and Clean It
	$path=$_FILES['userFile']['tmp_name'];
	$data=file_get_contents($path);
	$clean_data=addslashes($data);
	
	$filename=$loan.'-'.$type.'-'.$curr_date.'.pdf';
	
	$sql = 'INSERT INTO `file_data` (`file_name`, `data`) VALUES ("'.$filename.'", "'.$clean_data.'")';
	
	$rst=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);
	
	$sql='SELECT `auto_id` FROM `file_data` WHERE `file_name` LIKE "'.$filename.'"';
	$rsp=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);
	$auto_id=$rsp->fetch_array(MYSQLI_ASSOC);
	$inc=$auto_id['auto_id'];
	
	$sql='INSERT INTO `files` (`file_name`, `file_data`, `loan_num`, `file_type`, `file_date`, `file_size`) VALUES ("'.$filename.'", "'.$inc.'", '.$loan_id.', '.$type_id.', "'.$curr_date.'", '.$size.')';
	
	$rsp=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);
	
	if($rsp){
		echo '<h1 align="center" class="page-head-line">File was Uploaded</h1>';
	}
	
	echo '<form method="post" action="upload.php">';
	echo '<button type="submit" name="refresh">Go Back</button>';
	echo '</form>';

}
else{
	
	if(isset($_GET['loanNum'])){
		echo '<h1 align="center" class="page-head-line">Loan Existed, So I Redirected You!</h1>';
	}
	
	echo '<div id="page-inner">';
	echo '<h1 align="center" class="page-head-line">Upload a New File to DocStorage - Existing Loan</h1>';
	echo '<div class="panel-body">';
	echo '<form method="post" enctype="multipart/form-data" action="">';
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="10000000">';
	echo '<div class="form-group">';
	echo '<label for="loanNum" class="control-label">Loan Number</label>';
	echo '<input type="text" name="loanNum">';
	echo '</div>';
	echo '<div class="form-group">';
	echo '<label for="docType" class="control-label">Document Type</label>';
	echo '<select class="form-control" name="docType">';

	$dblink=db_connect('documents');

	$sql='SELECT `auto_id`, `type` FROM `file_type`';

	$result=$dblink->query($sql) or
		log_error('upload-existing.php', 'SQL', $dblink->error);

	while($row=$result->fetch_array(MYSQLI_ASSOC)){

		echo '<option value="'.$row['auto_id'].'">'.$row['type'].'</option>';

	}

	echo '</select></div><div class="form-group">';
	echo '<label class="control-label col-lg-4">File Upload</label>';
	echo '<div class="">';
	echo '<div class="fileupload fileupload-new" data-provides="fileupload">';
	echo '<div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>';
	echo '<div class="row">';
	echo '<div class="col-md-2">';
	echo '<span class="btn btn-file btn-primary">';
	echo '<span class="fileupload-new">Select File</span>';
	echo '<span class="fileupload-exists">Change</span>';
	echo '<input name="userFile" type="file" accept=".pdf">';
	echo '</span>';
	echo '</div>';
	echo '<div class="col-md-2">';
	echo '<a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>';
	echo '</div></div></div></div></div>';
	echo '<hr>';
	echo '<button type="submit" name="submit" value="submit" class="btn btn-lg btn-block btn-success">Upload File</button>';
	echo '</form>';
	echo '</div></div>';
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