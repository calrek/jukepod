<?php

/*
 * id: -) set this value to the ID of the directory that the script should process -) setting no value reads the directory set in MP3_ROOT in /config/config.php js_output: 1: refreshes the progress-bars 0: don't refresh progress_bars no_html_output: 1: suppresses html-output 0: sends html-output in_frame: 1: script runs in frame, flush output-buffer 0: script runs standalone, don't flush output-buffer reset_db: 1: empty the following tables: song, dir, album, artist, playlist_song 0: don't empty any tables scan_method: SCAN_ONLY_FILES_IN_CURRENT_DIR: scans only the files in the import-directory, skip directories SCAN_ONLY_NEW: scans only files and new sub-directories (and their contents) of the import-directory admin_key: set this to the md5-encoded admin-password if you are running this script from command line and are not logged in
 */
define ( "SCRIPT", "IMPORT" );
include "config/config.php";
$_SESSION ["sql_log"] = array ();

include "../inc/database.php";
include "../inc/functions.php";
include "../inc/import_album_cover.php";
include "../locale/language.php";

include "../security.php";

if (! $_SESSION ["permission"] ["read_files"]) {
	
	if (array_key_exists ( "admin_key", $_GET ) and $_GET ["admin_key"]) {
		$query = "SELECT ID FROM user WHERE type = 'admin' AND password = '" . $_GET ["admin_key"] . "'";
		$result = $db->query ( $query );
		
		if ($db->num_rows ( $result )) {
			$admin_id = $db->result ( $result, 0, 0 );
			$query = "SELECT value FROM user_permissions WHERE userID = " . $admin_id . " AND value = '1' AND name = 'read_files'";
			$result = $db->query ( $query );
			
			if (! $db->num_rows ( $result ))
				die ( $lang ["no_permission"] );
		} else {
			die ( $lang ["no_permission"] );
		}
	} else
		die ( $lang ["no_permission"] );
}

if ($_GET ["in_frame"]) {
	?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo lang("import_data_title"); ?></title>
<link rel="stylesheet" type="text/css"
	href="extjs3.1/resources/css/ext-all.css" />
</head>
<style type="text/css">
.dir_body {
	border: 0px;
	padding: 3px;
}
</style>
<body class="x-form-item x-window-mc dir_body">
<?php
}

$import_album_cover = new import_album_cover ( $_GET ["id"] );

$import_album_cover->process_albums ();

echo show_sql ();

if ($_GET ["in_frame"]) {
	?>
	</body>
</html>
<?php
}
?>
