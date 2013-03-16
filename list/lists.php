<?php 
include ("../config/config.php");
include ("../locale/language.php");
include ("../inc/database.php");
include ("../inc/functions.php");
include ("../security.php");

header("Content-type:text/javascript; charset=utf-8");

include("album_list.php");
include("artist_list.php");
include("mp3_list.php");
include("playlist.php");
?>
