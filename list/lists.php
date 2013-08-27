<?php  
include ("../config/config.php");
include ("../locale/language.php");
include ("../inc/database.php");
include ("../inc/functions.php");
include ("../security.php");

header("Content-Type: application/x-javascript; charset=utf-8"); 

include("album_list.js");
include("artist_list.js");
include("mp3_list.js");
include("playlist.js");
?>
