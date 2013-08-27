<?php

// ##########################################################################################################################
// CACHING Plugin by PA-S.de V.0.6
// (C) 2007 by http://www.PA-S.de
// Plugin: http://www.pa-s.de/php/codeschnipsel-MP3-NO-CACHING-PLUGIN-51.php
// ##########################################################################################################################
include "../config/config.php";
include "../inc/database.php";
include "../inc/functions.php";
include "../security.php";

if (! $_SESSION ["permission"] ["has_access"])
	die ();

$query = "SELECT filename,full_path FROM song WHERE ID = " . $_REQUEST ["ID"];
$result = $db->query ( $query );
$file = (MP3_PATH . "/" . $db->result ( $result, 0, "full_path" ) . "/" . $db->result ( $result, 0, "filename" ));

echo $file;
/*
 * if ( $file != NULL ) { $url_info = parse_url( $file ); $pfad = $url_info[path]; $array = explode( "/", $pfad ); $filename = end( $array ); $query = "UPDATE song SET last_played = ".time().", num_plays = num_plays + 1 WHERE ID = ".$_REQUEST["ID"]; $db->query( $query ); //header("Content-Transfer-Encoding: binary"); header( "Expires: 0" ); header( "Pragma:no-cache" ); header( "Cache-Control:private,no-store,no-cache,must-revalidate" ); header( "Content-Type: audio/mpeg" ); //header('content-type: application/octet-stream'); header( "Content-Length: " .(string)( filesize( $file ) ) ); header( "Accept-Ranges: bytes" ); //header("Content-Disposition: attachment; filename=".($filename).";"); if ( defined( "LOW_MEMORY_MODE" ) and LOW_MEMORY_MODE==1 ) { $fp=fopen( $file, "r" ); if ( $fp ) { while ( !feof( $fp ) ) { echo @fgets( $fp, 4096 ); } fclose( $fp ); } } else { echo file_get_contents( $file ); flush(); ob_flush(); } }
 */
?>
