<?php
include("config/config.php");
include("locale/language.php");
include("inc/database.php");
include("inc/functions.php");
include("security.php");

if(!$_SESSION["permission"]["download"])
	html_no_permission();

$query = "SELECT surdoc_url,filename,filesize FROM song WHERE ID = ".$_REQUEST["ID"];
$result = $db->query($query);
$file = $db->result($result,0,"surdoc_url");
$filename =  $db->result($result,0,"filename");
$filesize =  $db->result($result,0,"filesize");

if($file != NULL) {

	$query = "UPDATE song SET last_played = ".time().", num_plays = num_plays + 1 WHERE ID = ".$_REQUEST["ID"];
	$db->query($query);
	
	//header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Pragma:no-cache");
	header("Cache-Control:private,no-store,no-cache,must-revalidate");
	header("Content-Type: audio/mpeg");
	//header('content-type: application/octet-stream');
	header("Content-Length: " .(string)(filesize($file)) );
	header("Accept-Ranges: bytes");                
	header("Content-Disposition: attachment; filename=".($filename).";");

	if(defined("LOW_MEMORY_MODE") AND LOW_MEMORY_MODE==1) {
		$fp=fopen($file,"r");
		if ($fp) {
			while (!feof($fp)) { 
				echo (@fgets($fp, 4096)); 
			}
			fclose ($fp); 
		}
	}
	else {
		echo file_get_contents($file);
		flush();
		ob_flush();		
	}
}
?>

