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

//echo  $filename;

$fp = fopen($file,'r');
//$fc = file_get_contents($filename);

header("Expires: 0");
header("Pragma:no-cache");
header("Cache-Control:private,no-store,no-cache,must-revalidate");
//header("Content-Type: audio/mpeg");
header("Content-Length: " .basename($db->result($result,0,"file")) );
header('Content-Disposition: attachment; filename="'.basename($db->result($result,0,"file")).'"'); 

/*
//$filename = str_replace(ROOT,"",$filename);

if(defined("LOW_MEMORY_MODE") AND LOW_MEMORY_MODE==1)

	$fp=fopen($filename,"r");
	
else

	$fc = file_get_contents($filename);


header('Content-Disposition: attachment; filename="'.basename($db->result($result,0,"filename")).'"'); 

if(defined("LOW_MEMORY_MODE") AND LOW_MEMORY_MODE==1) {
	while (!feof($fp)) { 
		echo (@fgets($fp, 4096)); 
	}
	fclose ($fp);
} else
	echo $fc;
*/

$query = "UPDATE song SET num_downloads = num_downloads + 1 WHERE ID = ".$_REQUEST["ID"];
$db->query($query);

?>
