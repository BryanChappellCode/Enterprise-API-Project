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
	<div id="page-inner">
		<h1 class="page-head-line">Select the type of Upload</h1>
		<div class="panel-body">
			<div id="1" class="alert alert-info text-center default" onmouseover="addFocus(this.id)" onmouseout="removeFocus(this.id)">
				<h3>Upload to New Loan</h3>
				<a href="upload_new.php" class="btn btn-default">Upload New</a>
			</div>
			<div id="2" class="alert alert-info default text-center" onmouseover="addFocus(this.id)" onmouseout="removeFocus(this.id)">
				<h3>Upload to Existing Loan</h3>
				<a href="upload_existing.php" class="btn btn-default">Upload Existing</a>
			</div>
		</div>
	</div>
</body>
</html>



<?php

?>