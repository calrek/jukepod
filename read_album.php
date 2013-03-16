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
<title><?=lang("import_album_cover_title");?></title>

<script type="text/javascript" src="extjs/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="extjs/ext-all.js"></script>

<script type="text/javascript" src="javascript/progress-bar-album.php"></script>
<link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="css/progress-bar.css" />

</head>
<body class="x-border-layout-ct">
<script>
	var album_id = <?php if($_GET["id"]) echo $_GET["id"]; else echo "0"; ?>;
</script>
<div class="read_album">

	<div class="container">
		<div class="progress_row">
			<div class="label x-form-item"><?=lang("progress_scan");?>:</div>
			<div class="pb" id="pb_processing_album_div"></div>
		</div>
	
	</div>
	
	<div class="frame_container">
		<iframe id="iframeID" class="x-form-item x-window-mc"></iframe>
	</div>
</div>

</body>
</html>
