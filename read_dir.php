<?php
include("config/config.php");
include("locale/language.php");

include ("inc/database.php");
include ("inc/functions.php");
include ("security.php");

if(!$_SESSION["permission"]["read_files"])
	die("keine Berechtigung");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?=lang("import_data_title");?></title>

<script type="text/javascript" src="extjs/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="extjs/ext-all.js"></script>

<script type="text/javascript" src="javascript/progress-bar.php"></script>
<link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="css/progress-bar.css" />
</head>
<body class="x-border-layout-ct">
<script>
	var dir_id = <?php if($_GET["id"]) echo $_GET["id"]; else echo "0"; ?>;
	var scan_method = '<? if($_GET["scan_method"]) echo $_GET["scan_method"]; else echo ""; ?>';
	var reset_db = <?php if($_GET["reset_db"]) echo $_GET["reset_db"]; else echo "0"; ?>;
</script>
<div class="read_dir">
	<div class="container">
		<div class="progress_row">
			<div class="label x-form-item"><?=lang("progress_scan");?>:</div>
			<div class="pb" id="pb_scanning_div"></div>
		</div>
		
		<div class="progress_row">
			<div class="label x-form-item"><?=lang("progress_files");?>:</div>
			<div class="pb" id="pb_processing_file_div"></div>
		</div>
	
		<div class="progress_row">
			<div class="label x-form-item"><?=lang("progress_fields");?>:</div>
			<div class="pb" id="pb_processing_fields_div"></div>
		</div>

	</div>
	
	<div class="frame_container">
		<iframe id="iframeID" class="x-form-item x-window-mc"></iframe>
	</div>
</div>

</body>
</html>
