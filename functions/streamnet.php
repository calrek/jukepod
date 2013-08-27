<?php
include "../config/config.php";
include "../inc/database.php";
include "../inc/functions.php";
include "../security.php";

if (! $_SESSION ["permission"] ["has_access"])
	die ();

$query = "SELECT filename,full_path,filesize,surdoc FROM song WHERE ID = " . $_REQUEST ["ID"];
$result = $db->query ( $query );
// $file = ( COPYURL."/".$db->result( $result, 0, "full_path" )."/".$db->result( $result, 0, "filename" ) );
$size = $db->result ( $result, 0, "filesize" );
// $file = "http://www.jottacloud.com/p/calrek/56c15cd2f8214d1d8457b7ef11c537e5";
// echo $file;
$file = $db->result ( $result, 0, "surdoc" );

if ($file != NULL) {
	
	$query = "UPDATE song SET last_played = " . time () . ", num_plays = num_plays + 1 WHERE ID = " . $_REQUEST ["ID"];
	$db->query ( $query );
	
	header ( "Content-Transfer-Encoding: binary" );
	header ( "Cache-Control: max-age=2592000" );
	header ( "Content-Type: audio/mpeg" );
	// header('content-type: application/octet-stream');
	header ( "Content-Length: " . $size );
	header ( "Accept-Ranges: bytes" );
	// header("Content-Disposition: attachment; filename=".($filename).";");
	
	$fp = fopen ( $file, "r" );
	if ($fp) {
		while ( ! feof ( $fp ) ) {
			echo @fgets ( $fp, 4096 );
		}
		fclose ( $fp );
	}
}

?>
