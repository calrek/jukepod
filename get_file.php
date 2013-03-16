<?php
###########################################################################################################################
# CACHING Plugin by PA-S.de V.0.6
# (C) 2007 by http://www.PA-S.de
# Plugin: http://www.pa-s.de/php/codeschnipsel-MP3-NO-CACHING-PLUGIN-51.php
###########################################################################################################################
 
include("config/config.php");
include("inc/database.php");
include("inc/functions.php");
include("security.php");

$query = "SELECT surdoc_url, filesize FROM song WHERE ID = ".$_REQUEST["ID"];

$result = $db->query($query);

if($file != NULL) {

	$query = "UPDATE song SET last_played = ".time().", num_plays = num_plays + 1 WHERE ID = ".$_REQUEST["ID"];
	
	$db->query($query);


	//header("Content-Transfer-Encoding: binary");
	//header("Expires: 0");
	header("Pragma:no-cache");
	header("Cache-Control:private,no-store,no-cache,must-revalidate");
	header("Content-Type: audio/mpeg");
	//header('content-type: application/octet-stream');
	header("Content-Length: " .(string)(filesize($file)) );
	header("Accept-Ranges: bytes");                
	//header("Content-Disposition: attachment; filename=".($filename).";");

	readfile($file);

}
?>
