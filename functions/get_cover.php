<?php
include ("../config/config.php");
include ("../locale/language.php");
include ("../inc/database.php");
include ("../inc/functions.php");

$db = new db ();

$query = "SELECT cover_url FROM song WHERE ID = " . $_REQUEST ["ID"];
$result = $db->query ( $query );
$file = $db->result ( $result, 0, "cover_url" );

if (! empty ( $file )) {
	
	$bild = file_get_contents ( $file );
} else {
	
	$bild = file_get_contents ( "img/cover_no_album.gif" );
}

$length = strlen ( $bild );

header ( 'Last-Modified: ' . date ( 'r' ) );
header ( 'Accept-Ranges: bytes' );
header ( 'Content-Length: ' . $length );
header ( 'Content-Type: image/jpeg' );

echo $bild;

?>
