<?php 
if($_GET["lang"] and ($_GET["lang"]=="de" or $_GET["lang"]=="en"))
	$_SESSION["lang"] = $_GET["lang"];

if($_SESSION["lang"])
	include($_SESSION["lang"].".php");
else
	include(LANGUAGE.".php");

function lang($name,$addslashes = 0)
{
	global $lang;
	
	if(array_key_exists($name,$lang))
		$output = $lang[$name];
	else
		$output = "[".$name."]";
		
	if($addslashes)
		$output = addslashes($output);
	
	$output = str_replace("\r\n","",$output);
	$output = str_replace("\n","",$output);
	
	return $output;
}
?>
